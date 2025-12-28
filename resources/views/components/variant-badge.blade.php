@props(['variant'])

@php
    $name = strtolower($variant->name ?? '');
    
    // Determine badge type and styling
    if (str_contains($name, '1st edition') || str_contains($name, '1. edition')) {
        $type = '1st Edition';
        $classes = 'bg-yellow-100 text-yellow-800 border-yellow-300';
        $icon = '🥇';
    } elseif (str_contains($name, 'reverse') || str_contains($name, 'holo')) {
        $type = 'Reverse Holo';
        $classes = 'bg-purple-100 text-purple-800 border-purple-300';
        $icon = '✨';
    } elseif (str_contains($name, 'promo')) {
        $type = 'Promo';
        $classes = 'bg-red-100 text-red-800 border-red-300';
        $icon = '🎁';
    } elseif (str_contains($name, 'unlimited')) {
        $type = 'Unlimited';
        $classes = 'bg-gray-100 text-gray-800 border-gray-300';
        $icon = '';
    } else {
        $type = 'Normal';
        $classes = 'bg-blue-100 text-blue-800 border-blue-300';
        $icon = '';
    }
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    @if($icon)
        <span class="mr-1">{{ $icon }}</span>
    @endif
    {{ $type }}
</span>
