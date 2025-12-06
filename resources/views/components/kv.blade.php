@props(['items'])
<dl class="grid grid-cols-2 gap-x-4 gap-y-2">
    @foreach($items as $key => $value)
        <dt class="font-semibold">{{ $key }}</dt>
        <dd>{{ $value }}</dd>
    @endforeach
</dl>
