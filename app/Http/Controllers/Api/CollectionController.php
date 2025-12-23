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
     * Returns only the product_id array for checking ownership
     */
    public function getProductIds(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $productIds = UserCollection::where('user_id', Auth::id())
            ->pluck('product_id')
            ->toArray();

        return response()->json($productIds);
    }
}
