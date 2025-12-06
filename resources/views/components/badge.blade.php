
@props(['variant' => 'neutral'])
@php
$map = [
	'neutral' => 'inline-block px-3 py-1 rounded-full border border-gray-300 text-gray-700 bg-gray-50 text-xs font-semibold shadow-sm',
	'success' => 'inline-block px-3 py-1 rounded-full border border-green-300 text-green-700 bg-green-100 text-xs font-semibold shadow-sm',
	'warning' => 'inline-block px-3 py-1 rounded-full border border-yellow-300 text-yellow-700 bg-yellow-100 text-xs font-semibold shadow-sm',
	'danger' => 'inline-block px-3 py-1 rounded-full border border-red-300 text-red-700 bg-red-100 text-xs font-semibold shadow-sm',
];
@endphp
<span {{ $attributes->merge(['class' => $map[$variant] ?? $map['neutral']]) }}>{{ $slot }}</span>