@php
    $colors = [
        'draft'     => 'bg-gray-200 text-gray-700',
        'pending'   => 'bg-yellow-100 text-yellow-800',
        'approved'  => 'bg-blue-100 text-blue-800',
        'rejected'  => 'bg-red-100 text-red-800',
        'sent'      => 'bg-purple-100 text-purple-800',
        'received'  => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-gray-300 text-gray-600',
    ];
@endphp
<span class="px-2 py-1 text-xs rounded {{ $colors[$status] ?? 'bg-gray-200' }}">{{ ucfirst($status) }}</span>