<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['supplier', 'createdBy'])->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('order_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('order_date', '<=', $request->to);
        }
        if ($search = $request->input('search')) {
            $query->where('reference_number', 'like', "%{$search}%");
        }

        $orders = $query->latest()->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('company_name')->get();

        return view('purchase-orders.index', compact('orders', 'suppliers'));
    }

    public function create()
    {
        $this->authorize('create', PurchaseOrder::class);

        $suppliers = Supplier::active()->orderBy('company_name')->get();
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'price', 'supplier_id']);

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);

        $data = $this->validated($request);
        $submit = $request->input('action') === 'submit';

        $po = DB::transaction(function () use ($data, $request, $submit) {
            $supplier = Supplier::findOrFail($data['supplier_id']);

            $po = PurchaseOrder::create([
                'reference_number' => PurchaseOrder::generateReferenceNumber(),
                'supplier_id' => $supplier->id,
                'status' => $submit ? 'pending' : 'draft',
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date']
                    ?? now()->parse($data['order_date'])->addDays($supplier->lead_time_days),
                'tax' => $data['tax'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $po->items()->createMany($data['items']);
            $po->load('items')->calculateTotal();

            return $po;
        });

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "Purchase order {$po->reference_number} created.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'approvedBy']);

        return view('purchase-orders.show', ['order' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        $purchaseOrder->load('items');
        $suppliers = Supplier::active()->orderBy('company_name')->get();
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'price', 'supplier_id']);

        return view('purchase-orders.edit', [
            'order' => $purchaseOrder,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        $data = $this->validated($request);
        $submit = $request->input('action') === 'submit';

        DB::transaction(function () use ($data, $purchaseOrder, $submit) {
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'tax' => $data['tax'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => $submit ? 'pending' : 'draft',
            ]);

            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->createMany($data['items']);
            $purchaseOrder->load('items')->calculateTotal();
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('delete', $purchaseOrder);

        $purchaseOrder->delete(); // soft delete

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted.');
    }

    // ---- Workflow actions ----

    public function submit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('submit', $purchaseOrder);

        $purchaseOrder->update(['status' => 'pending']);

        return back()->with('success', 'Submitted for approval.');
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve', $purchaseOrder);

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Part 4: notify the creator by email here.

        return back()->with('success', 'Purchase order approved.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve', $purchaseOrder);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        $purchaseOrder->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase order rejected.');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('send', $purchaseOrder);

        $purchaseOrder->update(['status' => 'sent']);

        // Part 4: email the supplier here.

        return back()->with('success', 'Purchase order marked as sent to supplier.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('receive', $purchaseOrder);

        $request->validate([
            'received' => ['required', 'array'],
            'received.*' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->load('items.product');

            foreach ($purchaseOrder->items as $item) {
                $qty = (int) ($request->received[$item->id] ?? 0);
                $qty = min($qty, $item->outstanding_quantity); // never over-receive

                if ($qty <= 0) {
                    continue;
                }

                $item->increment('received_quantity', $qty);

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'user_id' => $request->user()->id,
                    'type' => 'in',
                    'quantity' => $qty,
                    'reference' => $purchaseOrder->reference_number,
                    'notes' => 'Received from purchase order',
                ]);

                $item->product->increment('quantity', $qty);
            }

            $purchaseOrder->load('items');
            if ($purchaseOrder->items->every(fn ($i) => $i->received_quantity >= $i->quantity)) {
                $purchaseOrder->update(['status' => 'received', 'received_date' => now()]);
            }
        });

        return back()->with('success', 'Items received and stock updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id,is_active,1,deleted_at,NULL'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }
}