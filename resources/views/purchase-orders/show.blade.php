<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $order->reference_number }}
                @include('purchase-orders._status', ['status' => $order->status])
            </h2>
            <a href="{{ route('purchase-orders.index') }}" class="text-sm text-gray-600">&larr; Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="p-3 bg-red-100 text-red-800 rounded text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div><dt class="text-gray-500">Supplier</dt>
                        <dd>{{ $order->supplier->company_name }}
                            @if($order->supplier->trashed())<span class="text-red-600">(deleted)</span>@endif</dd></div>
                    <div><dt class="text-gray-500">Order date</dt><dd>{{ $order->order_date->format('M d, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Expected delivery</dt><dd>{{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Created by</dt><dd>{{ $order->createdBy->name }}</dd></div>
                    <div><dt class="text-gray-500">Payment terms</dt><dd>{{ $order->supplier->payment_terms }}</dd></div>
                    <div><dt class="text-gray-500">Received on</dt><dd>{{ $order->received_date?->format('M d, Y') ?? '—' }}</dd></div>
                    <div class="md:col-span-3"><dt class="text-gray-500">Notes</dt><dd>{{ $order->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            {{-- Line items (and receiving form when allowed) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">Line items</h3>

                @can('receive', $order)
                    <form method="POST" action="{{ route('purchase-orders.receive', $order) }}">
                        @csrf
                @endcan

                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-600">
                        <tr>
                            <th class="py-2">Product</th><th>Ordered</th><th>Received</th>
                            <th>Unit price</th><th>Line total</th>
                            @can('receive', $order)<th>Receive now</th>@endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="py-2">{{ $item->product->sku }} - {{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->received_quantity }}</td>
                                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                <td>₱{{ number_format($item->line_total, 2) }}</td>
                                @can('receive', $order)
                                    <td>
                                        <input type="number" min="0" max="{{ $item->outstanding_quantity }}"
                                               name="received[{{ $item->id }}]" value="{{ $item->outstanding_quantity }}"
                                               class="w-24 border-gray-300 rounded text-sm">
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-right">
                        <tr><td colspan="4" class="pt-3 text-gray-500">Subtotal</td><td class="pt-3">₱{{ number_format($order->subtotal, 2) }}</td></tr>
                        <tr><td colspan="4" class="text-gray-500">Tax</td><td>₱{{ number_format($order->tax, 2) }}</td></tr>
                        <tr><td colspan="4" class="font-semibold">Total</td><td class="font-semibold">₱{{ number_format($order->total, 2) }}</td></tr>
                    </tfoot>
                </table>

                @can('receive', $order)
                        <button class="mt-4 px-4 py-2 bg-green-600 text-white rounded text-sm">Record receipt</button>
                    </form>
                @endcan
            </div>

            {{-- Approval history --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
                <h3 class="font-semibold mb-3">Approval history</h3>
                <p>Created by {{ $order->createdBy->name }} on {{ $order->created_at->format('M d, Y H:i') }}.</p>
                @if($order->approved_by)
                    <p>
                        {{ $order->status === 'rejected' ? 'Rejected' : 'Approved' }} by
                        {{ $order->approvedBy->name }} on {{ $order->approved_at->format('M d, Y H:i') }}.
                    </p>
                @endif
                @if($order->rejection_reason)
                    <p class="text-red-700">Reason: {{ $order->rejection_reason }}</p>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-2 items-start">
                @can('update', $order)
                    <a href="{{ route('purchase-orders.edit', $order) }}" class="px-4 py-2 bg-yellow-500 text-white rounded text-sm">Edit</a>
                @endcan

                @can('submit', $order)
                    <form method="POST" action="{{ route('purchase-orders.submit', $order) }}">@csrf
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Submit for approval</button>
                    </form>
                @endcan

                @can('approve', $order)
                    <form method="POST" action="{{ route('purchase-orders.approve', $order) }}">@csrf
                        <button class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('purchase-orders.reject', $order) }}" class="flex gap-2">@csrf
                        <input type="text" name="rejection_reason" placeholder="Rejection reason" required
                               class="border-gray-300 rounded text-sm">
                        <button class="px-4 py-2 bg-red-600 text-white rounded text-sm">Reject</button>
                    </form>
                @endcan

                @can('send', $order)
                    <form method="POST" action="{{ route('purchase-orders.send', $order) }}">@csrf
                        <button class="px-4 py-2 bg-purple-600 text-white rounded text-sm">Mark as sent</button>
                    </form>
                @endcan

                @can('delete', $order)
                    <form method="POST" action="{{ route('purchase-orders.destroy', $order) }}"
                          onsubmit="return confirm('Delete this draft?')">@csrf @method('DELETE')
                        <button class="px-4 py-2 bg-gray-200 text-red-700 rounded text-sm">Delete</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>