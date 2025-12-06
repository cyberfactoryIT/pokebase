@props(['name','label'=>null,'type'=>'text','value'=>null])
<label class="block">
  @if($label)
    <span class="label">
      {{ $label }}
      @if($attributes->has('required'))
        <span class="text-red-600 font-bold" title="{{ __('messages.required_field') }}">*</span>
        <span class="text-xs text-red-600 ml-1">{{ __('messages.required') }}</span>
      @endif
    </span>
  @endif
  <input
    type="{{ $type }}"
    name="{{ $name }}"
    value="{{ old($name,$value) }}"
    {{ $attributes->merge(['class'=>'mt-1 input']) }}
  >
  @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</label>
