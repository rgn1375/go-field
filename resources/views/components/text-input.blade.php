@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-gray-200 bg-white text-gray-900 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm px-4 py-3']) }}>
