
@props(['name','label'=>null,'options'=>[], 'placeholder'=>null, 'value'=>null])
<label class="block">
@if($label)
<span class="label font-semibold mb-1 block">
	{{ $label }}
	@if($attributes->has('required'))
		<span class="text-red-600 font-bold" title="{{ __('messages.required_field') }}">*</span>
		<span class="text-xs text-red-600 ml-1">{{ __('messages.required') }}</span>
	@endif
</span>
@endif
<select name="{{ $name }}" {{ $attributes->merge(['class'=>'mt-1 px-4 py-2 rounded-xl border border-gray-300 shadow-lg bg-white text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200']) }}>
@if($placeholder)
<option value="">{{ $placeholder }}</option>
@endif
@foreach($options as $key=>$text)
<option value="{{ $key }}" @selected(old($name,$value)==$key)>{{ $text }}</option>
@endforeach
{{ $slot }}
</select>
@error($name)
<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
</label>