@csrf

@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
        <ul class="list-disc ml-5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Company name *</label>
        <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name ?? '') }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Contact person *</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Email *</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Phone *</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Payment terms *</label>
        <select name="payment_terms" class="mt-1 w-full border-gray-300 rounded">
            @foreach(['LANBANK', 'PAYMAYA', 'GCASH', 'COD'] as $term)
                <option value="{{ $term }}" @selected(old('payment_terms', $supplier->payment_terms ?? 'COD') === $term)>{{ $term }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">Lead time (days) *</label>
        <input type="number" name="lead_time_days" min="1" max="365"
               value="{{ old('lead_time_days', $supplier->lead_time_days ?? 7) }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Minimum order amount *</label>
        <input type="number" step="0.01" min="0" name="minimum_order_amount"
               value="{{ old('minimum_order_amount', $supplier->minimum_order_amount ?? 0) }}"
               class="mt-1 w-full border-gray-300 rounded">
    </div>
    <div class="flex items-end">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $supplier->is_active ?? true)) class="rounded border-gray-300">
            <span class="ml-2 text-sm">Active</span>
        </label>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium">Address</label>
        <textarea name="address" rows="3" class="mt-1 w-full border-gray-300 rounded">{{ old('address', $supplier->address ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Save</button>
    <a href="{{ route('suppliers.index') }}" class="px-4 py-2 bg-gray-200 rounded text-sm">Cancel</a>
</div>