<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Purchase Orders</h2>
            @can('create', App\Models\PurchaseOrder::class)
                <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">+ New Purchase Order</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference no."
                       class="border-gray-300 rounded text-sm w-48">
                <select name="status" class="border-gray-300 rounded text-sm">
                    <option value="">All statuses</option>
                    @foreach(['draft','pending','approved','rejected','sent','received','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="supplier_id" class="border-gray-300 rounded text-sm">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->company_name }}</option>
                    @endforeach
                </select>
                <div>
                    <label class="block text-xs text-gray-500">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="border-gray-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="border-gray-300 rounded text-sm">
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded text-sm">Apply</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3">Order date</th>
                            <th class="px-4 py-3">Items</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Created by</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $order->reference_number }}</td>
                                <td class="px-4 py-3">{{ $order->supplier->company_name }}</td>
                                <td class="px-4 py-3">{{ $order->order_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3">{{ $order->items_count }}</td>
                                <td class="px-4 py-3">₱{{ number_format($order->total, 2) }}</td>
                                <td class="px-4 py-3">@include('purchase-orders._status', ['status' => $order->status])</td>
                                <td class="px-4 py-3">{{ $order->createdBy->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="text-indigo-600">View</a>
                                    @can('update', $order)
                                        | <a href="{{ route('purchase-orders.edit', $order) }}" class="text-yellow-600">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No purchase orders found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>