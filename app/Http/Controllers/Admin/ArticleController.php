<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Article::with('game');

        // Filter by game if specified
        if ($request->has('game_id') && $request->game_id) {
            $query->where('game_id', $request->game_id);
        }

        // Filter by category if specified
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        $articles = $query->orderBy('game_id')
            ->orderByRaw('sort_order is null, sort_order asc')
            ->orderByDesc('published_at')
            ->paginate(20);

        $games = Game::where('is_active', true)->get();
        $categories = Article::distinct()->pluck('category')->sort();

        return view('admin.articles.index', compact('articles', 'games', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $games = Game::where('is_active', true)->get();
        $categories = Article::distinct()->pluck('category')->sort();
        
        return view('admin.articles.create', compact('games', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'original_locale' => 'required|string|in:en,it,da',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'body' => 'required|string',
            'external_url' => 'nullable|url',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:1024', // Max 1MB
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $validated['image_path'] = 'storage/' . $path;
        }

        // Set published_at if is_published is true and no date is set
        if ($validated['is_published'] ?? false) {
            $validated['published_at'] = $validated['published_at'] ?? now();
        }

        Article::create($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return view('admin.articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        $games = Game::where('is_active', true)->get();
        $categories = Article::distinct()->pluck('category')->sort();
        
        return view('admin.articles.edit', compact('article', 'games', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'original_locale' => 'required|string|in:en,it,da',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'body' => 'required|string',
            'external_url' => 'nullable|url',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:1024', // Max 1MB
            'remove_image' => 'boolean',
        ]);

        // Handle image removal
        if ($request->remove_image && $article->image_path) {
            Storage::disk('public')->delete(str_replace('storage/', '', $article->image_path));
            $validated['image_path'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($article->image_path) {
                Storage::disk('public')->delete(str_replace('storage/', '', $article->image_path));
            }
            $path = $request->file('image')->store('articles', 'public');
            $validated['image_path'] = 'storage/' . $path;
        }

        // Set published_at if is_published is true and no date is set
        if ($validated['is_published'] ?? false) {
            $validated['published_at'] = $validated['published_at'] ?? $article->published_at ?? now();
        }

        $article->update($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        // Delete image if exists
        if ($article->image_path) {
            Storage::disk('public')->delete(str_replace('storage/', '', $article->image_path));
        }

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}

