<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suppliers</h2>
            <a href="{{ route('suppliers.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">+ Add Supplier</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            <form method="GET" class="mb-4 flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, contact, email"
                       class="border-gray-300 rounded text-sm w-64">
                <select name="status" class="border-gray-300 rounded text-sm">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <select name="sort" class="border-gray-300 rounded text-sm">
                    <option value="company_name" @selected(request('sort') === 'company_name')>Name</option>
                    <option value="lead_time_days" @selected(request('sort') === 'lead_time_days')>Lead time</option>
                    <option value="created_at" @selected(request('sort') === 'created_at')>Newest</option>
                </select>
                <select name="direction" class="border-gray-300 rounded text-sm">
                    <option value="asc" @selected(request('direction') !== 'desc')>Asc</option>
                    <option value="desc" @selected(request('direction') === 'desc')>Desc</option>
                </select>
                <button class="px-4 py-2 bg-gray-800 text-white rounded text-sm">Apply</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Company</th>
                            <th class="px-4 py-3">Contact</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Lead time</th>
                            <th class="px-4 py-3">Products</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $supplier->company_name }}</td>
                                <td class="px-4 py-3">{{ $supplier->contact_person }}</td>
                                <td class="px-4 py-3">{{ $supplier->email }}</td>
                                <td class="px-4 py-3">{{ $supplier->lead_time_days }} days</td>
                                <td class="px-4 py-3">{{ $supplier->products_count }}</td>
                                <td class="px-4 py-3">
                                    @if($supplier->is_active)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-indigo-600">View</a> |
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-yellow-600">Edit</a> |
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this supplier?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No suppliers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $suppliers->links() }}</div>
        </div>
    </div>
</x-app-layout>