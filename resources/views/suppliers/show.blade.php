<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $supplier->company_name }}</h2>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-yellow-500 text-white rounded text-sm">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">Supplier details</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Contact</dt><dd>{{ $supplier->full_contact }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd>{{ $supplier->is_active ? 'Active' : 'Inactive' }}</dd></div>
                    <div><dt class="text-gray-500">Payment terms</dt><dd>{{ $supplier->payment_terms }}</dd></div>
                    <div><dt class="text-gray-500">Lead time</dt><dd>{{ $supplier->lead_time_days }} days</dd></div>
                    <div><dt class="text-gray-500">Minimum order</dt><dd>₱{{ number_format($supplier->minimum_order_amount, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Address</dt><dd>{{ $supplier->address ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">Products supplied</h3>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-600">
                        <tr><th class="py-2">SKU</th><th>Name</th><th>Stock</th><th>Reorder level</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($supplier->products as $product)
                            <tr>
                                <td class="py-2">{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>{{ $product->reorder_level }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-gray-500">No products linked yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                Recent purchase orders and performance metrics will appear here after Parts 2 and 6.
            </div>
        </div>
    </div>
</x-app-layout>