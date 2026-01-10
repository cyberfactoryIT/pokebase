@props(['name','label'=>null,'rows'=>4,'value'=>null])
<label class="block">
@if($label)
<span class="label">{{ $label }}</span>
@endif
<textarea name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class'=>'mt-1 px-4 py-2 rounded-xl border border-gray-300 shadow-lg bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all duration-200']) }}>{{ old($name,$value) }}</textarea>
@error($name)
<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
</label>