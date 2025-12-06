@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('messages.promotions') }}</h2>
                <a href="{{ route('superadmin.promotions.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    <i class="fa fa-plus"></i>
                    <span>{{ __('messages.create_promotion') }}</span>
                </a>
            </div>
            @if($promotions->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ __('messages.name') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('messages.code') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('messages.type') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('messages.value') }}</th>
                                <th class="px-4 py-2 text-center">{{ __('messages.active') }}</th>
                                <th class="px-4 py-2 text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promotions as $promotion)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-800">{{ $promotion->name }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $promotion->code }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $promotion->type }}</td>
                                    <td class="px-4 py-2 text-right">{{ $promotion->value }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @if($promotion->active)
                                            <x-badge variant="success">{{ __('messages.yes') }}</x-badge>
                                        @else
                                            <x-badge variant="danger">{{ __('messages.no') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center flex gap-2 justify-center">
                                        <a href="{{ route('superadmin.promotions.edit', $promotion) }}" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-yellow-100 text-yellow-600 transition" title="{{ __('messages.edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('superadmin.promotions.destroy', $promotion) }}" onsubmit="return confirm('{{ __('messages.confirm_delete_promotion') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-red-100 text-red-600 transition" title="{{ __('messages.delete') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-right text-sm text-gray-500">
                                    {{ __('messages.showing_promotions', ['count' => $promotions->count(), 'total' => $promotions->total()]) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $promotions->links() }}
                </div>
            @else
                <p>No promotions available yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
