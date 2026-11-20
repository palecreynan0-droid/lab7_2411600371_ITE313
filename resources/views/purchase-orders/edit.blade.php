<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit {{ $order->reference_number }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('purchase-orders.update', $order) }}">
                    @method('PUT')
                    @include('purchase-orders._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>