@csrf

@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
        <ul class="list-disc ml-5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

@php
    $initialItems = old('items', isset($order)
        ? $order->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'quantity' => $i->quantity,
            'unit_price' => (float) $i->unit_price,
        ])->values()->all()
        : [['product_id' => '', 'quantity' => 1, 'unit_price' => 0]]);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium">Supplier *</label>
        <select name="supplier_id" class="mt-1 w-full border-gray-300 rounded">
            <option value="">Select supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    @selected(old('supplier_id', $order->supplier_id ?? '') == $supplier->id)>
                    {{ $supplier->company_name }} ({{ $supplier->lead_time_days }}d lead)
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">Order date *</label>
        <input type="date" name="order_date"
               value="{{ old('order_date', isset($order) ? $order->order_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Expected delivery</label>
        <input type="date" name="expected_delivery_date"
               value="{{ old('expected_delivery_date', isset($order) && $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : '') }}"
               class="mt-1 w-full border-gray-300 rounded">
        <p class="text-xs text-gray-500 mt-1">Leave blank to use the supplier's lead time.</p>
    </div>
</div>

<div class="mt-6"
     x-data="{
        products: {{ Js::from($products) }},
        items: {{ Js::from($initialItems) }},
        add() { this.items.push({ product_id: '', quantity: 1, unit_price: 0 }) },
        remove(i) { this.items.splice(i, 1) },
        setPrice(i) {
            const p = this.products.find(p => p.id == this.items[i].product_id);
            if (p) this.items[i].unit_price = parseFloat(p.price);
        },
        get subtotal() {
            return this.items.reduce((s, i) => s + (i.quantity * i.unit_price || 0), 0);
        }
     }">
    <h3 class="font-semibold mb-2">Line items</h3>

    <template x-for="(item, i) in items" :key="i">
        <div class="grid grid-cols-12 gap-2 mb-2 items-center">
            <select :name="`items[${i}][product_id]`" x-model="item.product_id" @change="setPrice(i)"
                    class="col-span-6 border-gray-300 rounded text-sm">
                <option value="">Select product</option>
                <template x-for="p in products" :key="p.id">
                    <option :value="p.id" x-text="p.sku + ' - ' + p.name" :selected="p.id == item.product_id"></option>
                </template>
            </select>
            <input type="number" min="1" :name="`items[${i}][quantity]`" x-model.number="item.quantity"
                   class="col-span-2 border-gray-300 rounded text-sm" placeholder="Qty">
            <input type="number" step="0.01" min="0" :name="`items[${i}][unit_price]`" x-model.number="item.unit_price"
                   class="col-span-3 border-gray-300 rounded text-sm" placeholder="Unit price">
            <button type="button" @click="remove(i)" x-show="items.length > 1"
                    class="col-span-1 text-red-600 text-sm">Remove</button>
        </div>
    </template>

    <button type="button" @click="add()" class="mt-2 px-3 py-1 bg-gray-200 rounded text-sm">+ Add item</button>

    <div class="mt-4 text-right text-sm">
        Subtotal: <strong>₱<span x-text="subtotal.toFixed(2)"></span></strong>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <div>
        <label class="block text-sm font-medium">Tax (amount)</label>
        <input type="number" step="0.01" min="0" name="tax" value="{{ old('tax', $order->tax ?? 0) }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium">Notes / terms</label>
        <textarea name="notes" rows="2" class="mt-1 w-full border-gray-300 rounded">{{ old('notes', $order->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button name="action" value="draft" class="px-4 py-2 bg-gray-700 text-white rounded text-sm">Save as draft</button>
    <button name="action" value="submit" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Save &amp; submit for approval</button>
    <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-gray-200 rounded text-sm">Cancel</a>
</div>