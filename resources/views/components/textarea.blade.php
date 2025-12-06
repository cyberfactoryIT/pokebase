@props(['name','label'=>null,'rows'=>4,'value'=>null])
<label class="block">
@if($label)
<span class="label">{{ $label }}</span>
@endif
<textarea name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class'=>'mt-1 input']) }}>{{ old($name,$value) }}</textarea>
@error($name)
<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
</label>