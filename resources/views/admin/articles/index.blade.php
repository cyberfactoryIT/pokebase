@extends('layouts.app')

@section('page_title', 'Manage Articles')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4">
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Articles Management</h2>
                <x-button as="a" href="{{ route('admin.articles.create') }}" icon="plus">
                    Create Article
                </x-button>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.articles.index') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Game</label>
                        <select name="game_id" class="w-full rounded-md border-gray-300">
                            <option value="">All Games</option>
                            @foreach($games as $game)
                                <option value="{{ $game->id }}" {{ request('game_id') == $game->id ? 'selected' : '' }}>
                                    {{ $game->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full rounded-md border-gray-300">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Title or excerpt..." class="w-full rounded-md border-gray-300">
                    </div>
                    <div class="flex items-end gap-2">
                        <x-button type="submit" variant="primary">Filter</x-button>
                        <x-button as="a" href="{{ route('admin.articles.index') }}" variant="neutral">Clear</x-button>
                    </div>
                </div>
            </form>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <x-table>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Game</th>
                            <th class="px-4 py-2 text-left">Category</th>
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-center">Published</th>
                            <th class="px-4 py-2 text-center">Order</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">
                                <span class="font-medium">{{ $article->game->name }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <x-badge variant="primary">{{ $article->category }}</x-badge>
                            </td>
                            <td class="px-4 py-2">
                                <div class="font-medium text-gray-800">{{ $article->title }}</div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $article->excerpt }}</div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($article->is_published)
                                    <x-badge variant="success">Yes</x-badge>
                                @else
                                    <x-badge variant="danger">No</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center text-gray-700">
                                {{ $article->sort_order ?? '-' }}
                            </td>
                            <td class="px-4 py-2 text-center flex gap-2 justify-center">
                                <x-button as="a" href="{{ route('admin.articles.edit', $article) }}" icon="pen" variant="neutral" title="Edit"></x-button>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" icon="trash" title="Delete"></x-button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No articles found. <a href="{{ route('admin.articles.create') }}" class="text-blue-600 hover:underline">Create your first article</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </div>

            <div class="mt-4">
                {{ $articles->links() }}
            </div>
        </x-card>
    </div>
</div>
@endsection
