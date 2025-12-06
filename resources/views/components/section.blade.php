<section {{ $attributes->merge(['class' => 'mb-8 mx-[20px]']) }}>
    <h1 class="text-2xl font-bold mb-4">{{ $title ?? '' }}</h1>
    {{ $slot }}
</section>
