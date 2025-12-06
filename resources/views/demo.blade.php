@extends('layouts.app')
@section('title', __('messages.demo'))
@section('page_title', __('messages.demo'))

@section('content')

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto">
        <x-card>
          {{-- Smoke test: se vedi questo badge, i componenti funzionano --}}
          <x-badge variant="success">Components loaded</x-badge>
      
          <div class="grid gap-4 mt-4">
            <x-card>
              <form class="grid gap-3" method="post" action="#">
                @csrf
                <x-input name="name" :label="__('messages.name')"/>
                <x-select name="status" :label="__('messages.status')" :options="['draft'=>__('messages.status_draft'),'active'=>__('messages.status_active')]" placeholder="—" />
                <x-textarea name="notes" :label="__('messages.notes')" rows="3" />
                <div class="flex items-center gap-2">
                  <x-button type="submit">@lang('messages.save')</x-button>
                  <x-button variant="neutral" type="button">@lang('messages.cancel')</x-button>
                  <x-badge variant="success">@lang('messages.ok')</x-badge>
                </div>
              </form>
            </x-card>
      
            <x-modal :title="__('messages.modal_title')">
              <x-slot name="trigger">
                <x-button>@lang('messages.open_modal')</x-button>
              </x-slot>
              <p class="text-sm text-gray-700">@lang('messages.modal_body')</p>
            </x-modal>
          </div>
        </x-card>
    </div>
</div>
@endsection
