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
    {{ $attributes->merge(['class'=>'mt-1 px-4 py-2 rounded-xl border border-gray-300 shadow-lg bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all duration-200']) }}
  >
  @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</label>
