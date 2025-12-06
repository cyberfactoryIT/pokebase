{{-- resources/views/admin/helps/index.blade.php --}}

@extends('layouts.app')
@section('content')
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">{{ __('faq.title') }}</h1>
  <x-button as="a" href="{{ route('helps.create') }}" variant="primary" icon="plus">{{ __('faq.new') }}</x-button>
  </div>
  <table class="w-full mt-4 border">
    <thead>
      <tr>
        <th>{{ __('faq.helper.key') }}</th>
        <th>{{ __('faq.helper.icon') }}</th>
        <th>{{ __('faq.helper.active') }}</th>
        <th>{{ __('faq.helper.updated') }}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    @foreach($helps as $h)
      <tr class="border-t">
        <td class="p-2 font-mono">{{ $h->key }}</td>
        <td class="p-2">{{ $h->icon }}</td>
        <td class="p-2">{{ $h->is_active ? __('faq.yes') : __('faq.no') }}</td>
        <td class="p-2 text-sm text-gray-500">{{ $h->updated_at }}</td>
        <td class="p-2 text-right">
          <x-button as="a" href="{{ route('helps.edit',$h) }}" variant="neutral" icon="edit">{{ __('faq.edit') }}</x-button>
          <form class="inline" method="POST" action="{{ route('helps.destroy',$h) }}">
            @csrf @method('DELETE')
            <x-button type="submit" variant="danger" icon="trash" class="ml-2">{{ __('faq.delete') }}</x-button>
          </form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
  <div class="mt-4">{{ $helps->links() }}</div>
@endsection
