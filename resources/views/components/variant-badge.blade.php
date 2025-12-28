@props(['variant'])

@php
    $name = strtolower($variant->name ?? '');
    
    // Determine badge type and styling
    if (str_contains($name, '1st edition') || str_contains($name, '1. edition')) {
        $type = 'first_edition';
        $classes = 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30';
        $icon = '1st';
    } elseif (str_contains($name, 'reverse') || str_contains($name, 'holo')) {
        $type = 'reverse_holo';
        $classes = 'bg-purple-500/20 text-purple-300 border-purple-500/30';
        $icon = '✨';
    } elseif (str_contains($name, 'promo')) {
        $type = 'promo';
        $classes = 'bg-red-500/20 text-red-300 border-red-500/30';
        $icon = '🎁';
    } elseif (str_contains($name, 'unlimited')) {
        $type = 'unlimited';
        $classes = 'bg-gray-500/20 text-gray-300 border-gray-500/30';
        $icon = '';
    } else {
        $type = 'normal';
        $classes = 'bg-blue-500/20 text-blue-300 border-blue-500/30';
        $icon = '';
    }
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md border ' . $classes]) }}>
    @if($icon)
        <span>{{ $icon }}</span>
    @endif
    {{ __('variants.' . $type) }}
</span>
