
@props(['type' => 'info', 'heading' => null, 'icon' => null])
@php
    $colors = [
        'success' => ['bg' => 'bg-green-50', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
        'danger' => ['bg' => 'bg-red-50', 'text' => 'text-red-800', 'icon' => 'fa-times-circle'],
        'warning' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-800', 'icon' => 'fa-exclamation-circle'],
        'info' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'icon' => 'fa-info-circle'],
    ];
    $c = $colors[$type] ?? $colors['info'];
    $iconClass = $icon ? $icon : $c['icon'];
@endphp
<div class="p-4 rounded-xl mb-4 flex gap-3 items-start {{ $c['bg'] }} {{ $c['text'] }} shadow-lg border border-gray-200">
    <div class="pt-1">
        <i class="fa {{ $iconClass }} text-xl"></i>
    </div>
    <div>
        @if($heading)
            <div class="font-bold text-base mb-1">{{ $heading }}</div>
        @endif
        <div>{{ $slot }}</div>
    </div>
</div>
