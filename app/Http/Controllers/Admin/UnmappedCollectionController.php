<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TcgcsvProduct;
use App\Models\UserCollection;
use App\Models\DeckCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UnmappedCollectionController extends Controller
{
    /**
     * Display collection and deck cards without CardMarket mapping
     */
    public function index(Request $request): View
    {
        // Get unique product_ids from user_collection
        $collectionProductIds = UserCollection::distinct('product_id')
            ->pluck('product_id');
        
        // Get unique product_ids from deck_cards
        $deckProductIds = DeckCard::distinct('product_id')
            ->pluck('product_id');
        
        // Merge both collections
        $allProductIds = $collectionProductIds->merge($deckProductIds)->unique();
        
        // Get products that are in collections/decks but have no cardmarket_product_id
        $query = TcgcsvProduct::whereIn('product_id', $allProductIds)
            ->whereNull('cardmarket_product_id')
            ->with(['group:group_id,name,abbreviation', 'prices'])
            ->orderBy('group_id', 'desc')
            ->orderBy('card_number', 'asc');
        
        // Add search filter
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('card_number', 'like', "%{$search}%");
            });
        }
        
        $unmappedCards = $query->paginate(50);
        
        // Get statistics
        $stats = [
            'total_in_collections' => $collectionProductIds->count(),
            'total_in_decks' => $deckProductIds->count(),
            'total_unique' => $allProductIds->count(),
            'unmapped_count' => TcgcsvProduct::whereIn('product_id', $allProductIds)
                ->whereNull('cardmarket_product_id')
                ->count(),
            'mapped_count' => TcgcsvProduct::whereIn('product_id', $allProductIds)
                ->whereNotNull('cardmarket_product_id')
                ->count(),
        ];
        
        return view('admin.unmapped-collection.index', compact('unmappedCards', 'stats'));
    }
}
