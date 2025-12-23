<?php

namespace App\Http\Controllers;

use App\Models\UserCollection;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CollectionController extends Controller
{
    /**
     * Display user's collection
     */
    public function index(): View
    {
        $collection = UserCollection::where('user_id', Auth::id())
            ->with('card.group')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        $stats = [
            'total_cards' => UserCollection::where('user_id', Auth::id())->sum('quantity'),
            'unique_cards' => UserCollection::where('user_id', Auth::id())->count(),
            'foil_cards' => UserCollection::where('user_id', Auth::id())
                ->where('is_foil', true)
                ->sum('quantity'),
        ];

        return view('collection.index', compact('collection', 'stats'));
    }

    /**
     * Add a card to user's collection
     */
    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:tcgcsv_products,product_id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $validated['quantity'] ?? 1);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a card from collection
     */
    public function remove(UserCollection $collectionItem): RedirectResponse
    {
        // Authorization check
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $collectionItem->delete();

        return back()->with('success', 'Card removed from collection!');
    }

    /**
     * Update card quantity or details
     */
    public function update(Request $request, UserCollection $collectionItem): RedirectResponse
    {
        // Authorization check
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $collectionItem->update($validated);

        return back()->with('success', 'Collection item updated!');
    }

    /**
     * Check if a card is in user's collection
     */
    public function checkCard(int $productId)
    {
        $items = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->get();

        return response()->json([
            'in_collection' => $items->isNotEmpty(),
            'total_quantity' => $items->sum('quantity'),
            'items' => $items,
        ]);
    }
}
