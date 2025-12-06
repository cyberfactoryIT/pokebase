{{-- resources/views/admin/helps/form.blade.php --}}
@extends('layouts.app')
@section('content')
  <h1 class="text-xl font-semibold">
    {{ $help->exists ? __('faq.helper.edit') : __('faq.helper.create') }} {{ __('faq.helper.help') }}
  </h1>
  <form class="mt-4 space-y-4" method="POST" action="{{ $help->exists ? route('helps.update',$help) : route('helps.store') }}">
    @csrf
    @if($help->exists) @method('PUT') @endif


    <div>
      <label class="block text-sm">{{ __('faq.helper.key') }}</label>
      <input name="key" class="w-full border p-2" value="{{ old('key',$help->key) }}" required>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm">{{ __('faq.helper.icon') }}</label>
        <input name="icon" class="w-full border p-2" value="{{ old('icon',$help->icon) }}">
      </div>
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active',$help->is_active) ? 'checked' : '' }}>
          <span>{{ __('faq.helper.active') }}</span>
        </label>
      </div>
    </div>

    @foreach(['title'=>__('faq.helper.title'),'short'=>__('faq.helper.short'),'long'=>__('faq.helper.long')] as $field => $label)
      <fieldset class="border p-3 rounded">
        <legend class="text-sm font-medium">{{ $label }}</legend>
        @foreach(['en','it','da'] as $loc)
          <div class="mt-2">
            <label class="block text-xs uppercase">{{ $loc }}</label>
            @if($field === 'long')
              <textarea name="{{ $field.'['.$loc.']' }}" rows="5" class="w-full border p-2">{{ old($field.'.'.$loc, data_get($help,$field.'.'.$loc)) }}</textarea>
            @else
              <input name="{{ $field.'['.$loc.']' }}" class="w-full border p-2" value="{{ old($field.'.'.$loc, data_get($help,$field.'.'.$loc)) }}">
            @endif
          </div>
        @endforeach
      </fieldset>
    @endforeach

    <fieldset class="border p-3 rounded">
      <legend class="text-sm font-medium">{{ __('faq.helper.links') }}</legend>
      {{-- semplice JSON libero --}}
      <textarea name="links" rows="4" class="w-full border p-2" placeholder='[{"route":"2fa.show","label":{"en":"Set up 2FA","it":"Configura 2FA","da":"Konfigurer 2FA"}}]'>{{ old('links', $help->links ? json_encode($help->links, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '') }}</textarea>
      <p class="text-xs text-gray-500 mt-1">{{ __('faq.helper.links_hint') }}</p>
    </fieldset>

    <div class="flex gap-2 mt-6">
      <x-button type="submit" variant="primary" icon="save">{{ __('faq.helper.save') }}</x-button>
      <x-button as="a" href="{{ route('helps.index') }}" variant="neutral" icon="arrow-left">{{ __('faq.helper.back') }}</x-button>
    </div>
    </div>
    </div>
  </form>
@endsection
