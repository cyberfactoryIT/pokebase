
@props(['variant' => 'primary', 'as' => 'button', 'href' => null, 'type' => 'button', 'icon' => null])
@php
$variants = [
    'primary' => 'bg-blue-600 text-white border border-blue-600 hover:bg-blue-700 hover:text-white focus:ring-2 focus:ring-blue-200',
    'success' => 'bg-green-600 text-white border border-green-600 hover:bg-green-700 hover:text-white focus:ring-2 focus:ring-green-200',
    'warning' => 'bg-yellow-500 text-white border border-yellow-500 hover:bg-yellow-600 hover:text-white focus:ring-2 focus:ring-yellow-200',
    'danger' => 'bg-red-600 text-white border border-red-600 hover:bg-red-700 hover:text-white focus:ring-2 focus:ring-red-200',
    'neutral' => 'bg-gray-200 text-gray-700 border border-gray-300 hover:bg-gray-300 hover:text-gray-800 focus:ring-2 focus:ring-gray-200',
];
$classes = 'inline-flex items-center px-4 py-2 rounded-lg font-semibold text-xs tracking-wide shadow transition-all duration-200 ' . ($variants[$variant] ?? $variants['primary']);
@endphp
@if($as === 'a')
<a href="{{ $href }}" class="{{ $classes }}" {{ $attributes }}>
    @if($icon)
        <i class="fa fa-{{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</a>
@else
<button type="{{ $type }}" class="{{ $classes }}" {{ $attributes }}>
    @if($icon)
        <i class="fa fa-{{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</button>
@endif