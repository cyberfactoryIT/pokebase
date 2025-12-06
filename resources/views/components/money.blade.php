@props(['cents', 'currency' => 'EUR'])
<span>
    {{ number_format($cents / 100, 2) }} {{ $currency }}
</span>
