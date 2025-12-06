@extends('layouts.app')

@section('page_title', __('messages.Projects'))

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('messages.nav.projects') }}</h2>
                @can('create', \App\Models\Project::class)
                    <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                        <i class="fa fa-plus"></i>
                        <span>{{ __('messages.projects.new_Project') }}</span>
                    </a>
                @endcan
            </div>
            <form method="GET" class="mb-6 flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search projects..." class="input w-64 border rounded px-3 py-2" />
                <button type="submit" class="btn bg-gray-100 px-4 py-2 rounded hover:bg-gray-200">Search</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('messages.name') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.code') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.responsible') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('messages.active') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('messages.dates') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($projects as $project)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $project->name }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $project->code }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ optional($project->responsible)->name }}</td>
                            <td class="px-4 py-2 text-center">
                                @if($project->is_active)
                                    <span class="inline-block px-2 py-1 rounded bg-green-100 text-green-800 text-xs">Yes</span>
                                @else
                                    <span class="inline-block px-2 py-1 rounded bg-red-100 text-red-800 text-xs">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center text-gray-700">
                                {{ optional($project->starts_at)->format('d/m/Y') ?? '-' }} — {{ optional($project->ends_at)->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 text-center flex gap-2 justify-center">
                                @can('view', $project)
                                    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-blue-100 text-blue-600 transition" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-yellow-100 text-yellow-600 transition" title="Edit">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('delete', $project)
                                    <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-red-100 text-red-600 transition" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
