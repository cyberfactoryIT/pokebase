<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    /**
     * Get user's collection product IDs
     * 
     * Returns both product_id and tcgdex_card_id arrays for checking ownership
     */
    public function getProductIds(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'product_ids' => [],
                'tcgdex_card_ids' => []
            ]);
        }

        $collection = UserCollection::where('user_id', Auth::id())
            ->select('product_id', 'tcgdex_card_id')
            ->get();

        $productIds = $collection->whereNotNull('product_id')->pluck('product_id')->toArray();
        $tcgdexCardIds = $collection->whereNotNull('tcgdex_card_id')->pluck('tcgdex_card_id')->toArray();

        return response()->json([
            'product_ids' => $productIds,
            'tcgdex_card_ids' => $tcgdexCardIds
        ]);
    }
}
