<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvProduct;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TcgItemInteractionController extends Controller
{
    /**
     * Toggle like on a product
     */
    public function toggleLike(Request $request, int $productId): JsonResponse
    {
        $user = Auth::user();
        $product = TcgcsvProduct::where('product_id', $productId)->firstOrFail();

        DB::beginTransaction();
        try {
            $exists = DB::table('user_likes')
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                // Unlike
                DB::table('user_likes')
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->delete();
                
                $status = 'unliked';
            } else {
                // Like
                DB::table('user_likes')->insert([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'created_at' => now(),
                ]);
                
                $status = 'liked';
            }

            DB::commit();

            // Get total likes count
            $likesCount = DB::table('user_likes')
                ->where('product_id', $productId)
                ->count();

            return response()->json([
                'status' => $status,
                'count' => $likesCount,
                'message' => __('tcg/interactions.like_' . $status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('tcg/interactions.error_generic'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle wishlist on a product
     */
    public function toggleWishlist(Request $request, int $productId): JsonResponse
    {
        $user = Auth::user();
        $product = TcgcsvProduct::where('product_id', $productId)->firstOrFail();

        DB::beginTransaction();
        try {
            $exists = DB::table('user_wishlist_items')
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                // Remove from wishlist
                DB::table('user_wishlist_items')
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->delete();
                
                $status = 'removed';
            } else {
                // Add to wishlist
                DB::table('user_wishlist_items')->insert([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'created_at' => now(),
                ]);
                
                $status = 'added';
            }

            DB::commit();

            return response()->json([
                'status' => $status,
                'message' => __('tcg/interactions.wishlist_' . $status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('tcg/interactions.error_generic'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle watch on a product
     */
    public function toggleWatch(Request $request, int $productId): JsonResponse
    {
        $user = Auth::user();
        $product = TcgcsvProduct::where('product_id', $productId)->firstOrFail();

        DB::beginTransaction();
        try {
            $exists = DB::table('user_watch_items')
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                // Stop watching
                DB::table('user_watch_items')
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->delete();
                
                $status = 'unwatched';
            } else {
                // Start watching
                DB::table('user_watch_items')->insert([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'created_at' => now(),
                ]);
                
                $status = 'watched';
            }

            DB::commit();

            return response()->json([
                'status' => $status,
                'message' => __('tcg/interactions.watch_' . $status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('tcg/interactions.error_generic'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show user's liked cards
     */
    public function likesList(Request $request): View
    {
        $currentGame = $request->attributes->get('currentGame');
        $user = Auth::user();
        $catalogBackend = catalog_backend();

        $query = $user->likedProducts()
            ->with(['group', 'prices', 'rapidapiCard'])
            ->orderBy('user_likes.created_at', 'desc');

        if ($currentGame) {
            $query->where('tcgcsv_products.game_id', $currentGame->id);
        }

        // Filter by catalog backend: only show cards from current backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('user_likes.tcgdex_card_id');
        } else {
            $query->whereNotNull('user_likes.product_id');
        }

        $likedProducts = $query->paginate(50);

        return view('tcg.interactions.likes', compact('likedProducts', 'currentGame'));
    }

    /**
     * Show user's wishlist
     */
    public function wishlistList(Request $request): View
    {
        $currentGame = $request->attributes->get('currentGame');
        $user = Auth::user();
        $catalogBackend = catalog_backend();

        $query = $user->wishlistProducts()
            ->with(['group', 'prices', 'rapidapiCard'])
            ->orderBy('user_wishlist_items.created_at', 'desc');

        if ($currentGame) {
            $query->where('tcgcsv_products.game_id', $currentGame->id);
        }

        // Filter by catalog backend: only show cards from current backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('user_wishlist_items.tcgdex_card_id');
        } else {
            $query->whereNotNull('user_wishlist_items.product_id');
        }

        $wishlistProducts = $query->paginate(50);

        return view('tcg.interactions.wishlist', compact('wishlistProducts', 'currentGame'));
    }

    /**
     * Show user's watched cards (Osservazione)
     */
    public function watchList(Request $request): View
    {
        $currentGame = $request->attributes->get('currentGame');
        $user = Auth::user();
        $catalogBackend = catalog_backend();

        $query = $user->watchedProducts()
            ->with(['group', 'prices', 'rapidapiCard'])
            ->orderBy('user_watch_items.created_at', 'desc');

        if ($currentGame) {
            $query->where('tcgcsv_products.game_id', $currentGame->id);
        }

        // Filter by catalog backend: only show cards from current backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('user_watch_items.tcgdex_card_id');
        } else {
            $query->whereNotNull('user_watch_items.product_id');
        }

        $watchedProducts = $query->paginate(50);

        return view('tcg.interactions.osservazione', compact('watchedProducts', 'currentGame'));
    }

    // ===== TCGDEX METHODS =====

    /**
     * Toggle like on a TCGDEX card
     */
    public function toggleLikeTcgdex(Request $request, string $cardId): JsonResponse
    {
        $user = Auth::user();
        $card = TcgdxCard::where('tcgdex_id', $cardId)->firstOrFail();

        DB::beginTransaction();
        try {
            $exists = DB::table('user_likes')
                ->where('user_id', $user->id)
                ->where('tcgdex_card_id', $card->id)
                ->exists();

            if ($exists) {
                // Unlike
                DB::table('user_likes')
                    ->where('user_id', $user->id)
                    ->where('tcgdex_card_id', $card->id)
                    ->delete();
                
                $status = 'unliked';
            } else {
                // Like
                DB::table('user_likes')->insert([
                    'user_id' => $user->id,
                    'tcgdex_card_id' => $card->id,
                    'created_at' => now(),
                ]);
                
                $status = 'liked';
            }

            DB::commit();

            return response()->json([
                'status' => $status,
                'message' => __('tcg/interactions.like_' . $status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('tcg/interactions.error_generic'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle wishlist on a TCGDEX card
     */
    public function toggleWishlistTcgdex(Request $request, string $cardId): JsonResponse
    {
        $user = Auth::user();
        $card = TcgdxCard::where('tcgdex_id', $cardId)->firstOrFail();

        DB::beginTransaction();
        try {
            $exists = DB::table('user_wishlist_items')
                ->where('user_id', $user->id)
                ->where('tcgdex_card_id', $card->id)
                ->exists();

            if ($exists) {
                // Remove from wishlist
                DB::table('user_wishlist_items')
                    ->where('user_id', $user->id)
                    ->where('tcgdex_card_id', $card->id)
                    ->delete();
                
                $status = 'removed';
            } else {
                // Add to wishlist
                DB::table('user_wishlist_items')->insert([
                    'user_id' => $user->id,
                    'tcgdex_card_id' => $card->id,
                    'created_at' => now(),
                ]);
                
                $status = 'added';
            }

            DB::commit();

            return response()->json([
                'status' => $status,
                'message' => __('tcg/interactions.wishlist_' . $status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('tcg/interactions.error_generic'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle watch on a TCGDEX card
     */
    public function toggleWatchTcgdex(Request $request, string $cardId): JsonResponse
    {
        $user = Auth::user();
        $card = TcgdxCard::where('tcgdex_id', $cardId)->firstOrFail();

        DB::beginTransaction();
        try {
            $exists = DB::table('user_watch_items')
                ->where('user_id', $user->id)
                ->where('tcgdex_card_id', $card->id)
                ->exists();

            if ($exists) {
                // Stop watching
                DB::table('user_watch_items')
                    ->where('user_id', $user->id)
                    ->where('tcgdex_card_id', $card->id)
                    ->delete();
                
                $status = 'unwatched';
            } else {
                // Start watching
                DB::table('user_watch_items')->insert([
                    'user_id' => $user->id,
                    'tcgdex_card_id' => $card->id,
                    'created_at' => now(),
                ]);
                
                $status = 'watched';
            }

            DB::commit();

            return response()->json([
                'status' => $status,
                'message' => __('tcg/interactions.watch_' . $status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('tcg/interactions.error_generic'),
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
