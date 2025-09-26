@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-gray-200 bg-white text-black focus:border-sky-500 focus:ring-sky-500 rounded-md shadow-sm']) }}>
