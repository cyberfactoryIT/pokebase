@extends('layouts.app')
@section('page_title', __('messages.Project_Details'))
@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-2xl font-semibold mb-4">{{ $project->name }}</h2>
    <div class="mb-2"><strong>{{ __('messages.Code') }}:</strong> {{ $project->code }}</div>
    <div class="mb-2"><strong>{{ __('messages.Description') }}:</strong> {{ $project->description }}</div>
    <div class="mb-2"><strong>{{ __('messages.Responsible') }}:</strong> {{ optional($project->responsible)->name }} ({{ optional($project->responsible)->email }})</div>
    <div class="mb-2"><strong>{{ __('messages.Active') }}:</strong> {{ $project->is_active ? __('messages.Yes') : __('messages.No') }}</div>
    <div class="mb-2"><strong>{{ __('messages.Billable') }}:</strong> {{ $project->billable ? __('messages.Yes') : __('messages.No') }}</div>
    <div class="mb-2"><strong>{{ __('messages.Dates') }}:</strong> {{ optional($project->starts_at)->format('Y-m-d') ?? '-' }} — {{ optional($project->ends_at)->format('Y-m-d') ?? '-' }}</div>
    <div class="mt-6 flex gap-2">
        @can('update', $project)
                <x-button as="a" href="{{ route('projects.edit', $project) }}" variant="warning" icon="pen">{{ __('Edit') }}</x-button>
        @endcan
        @can('delete', $project)
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" icon="trash">{{ __('Delete') }}</x-button>
                </form>
        @endcan
        <x-button as="a" href="{{ route('projects.index') }}" variant="secondary" icon="arrow-left">{{ __('Back to list') }}</x-button>
    </div>
</div>
@endsection
