@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-gray-700 font-bold mb-2']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-red-500 ">*</span>
    @endif
</label>
