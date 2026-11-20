<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()->withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sort = in_array($request->sort, ['company_name', 'lead_time_days', 'created_at'])
            ? $request->sort : 'company_name';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';

        $suppliers = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('products');
        // Recent purchase orders and performance metrics are added in Parts 2 and 6

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validated($request, $supplier);
        $data['is_active'] = $request->boolean('is_active');

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // The pending purchase order check is added in Part 2,
        // once the PurchaseOrder model exists.

        $supplier->delete(); // soft delete

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted.');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('suppliers', 'email')->ignore($supplier)],
            'phone' => ['required', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'address' => ['nullable', 'string'],
            'payment_terms' => ['required', 'string', 'max:50'],
            'lead_time_days' => ['required', 'integer', 'min:1', 'max:365'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}