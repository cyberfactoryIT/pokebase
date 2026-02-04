<?php

namespace App\Http\Controllers;

use App\Models\Cmapi\CmapiCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CmapiInteractionController extends Controller
{
    /**
     * Toggle like on a CMAPI card
     */
    public function toggleLike(Request $request, string $game, int $cardId)
    {
        $user = Auth::user();
        $card = CmapiCard::findOrFail($cardId);

        DB::beginTransaction();
        try {
            $exists = DB::table('user_likes')
                ->where('user_id', $user->id)
                ->where('cmapi_card_id', $card->cmapi_id)
                ->exists();

            if ($exists) {
                // Unlike
                DB::table('user_likes')
                    ->where('user_id', $user->id)
                    ->where('cmapi_card_id', $card->cmapi_id)
                    ->delete();
                
                $status = 'unliked';
            } else {
                // Like
                DB::table('user_likes')->insert([
                    'user_id' => $user->id,
                    'cmapi_card_id' => $card->cmapi_id,
                    'created_at' => now(),
                ]);
                
                $status = 'liked';
            }

            DB::commit();

            // Get total likes count
            $likesCount = DB::table('user_likes')
                ->where('cmapi_card_id', $card->cmapi_id)
                ->count();

            $message = $status === 'liked' 
                ? __('tcg/interactions.like_liked') 
                : __('tcg/interactions.like_unliked');

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => $status,
                    'count' => $likesCount,
                    'message' => $message,
                ]);
            }

            // Otherwise redirect back with message
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => __('tcg/interactions.error_generic'),
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', __('tcg/interactions.error_generic'));
        }
    }

    /**
     * Toggle wishlist on a CMAPI card
     */
    public function toggleWishlist(Request $request, string $game, int $cardId)
    {
        $user = Auth::user();
        $card = CmapiCard::findOrFail($cardId);

        DB::beginTransaction();
        try {
            $exists = DB::table('user_wishlist_items')
                ->where('user_id', $user->id)
                ->where('cmapi_card_id', $card->cmapi_id)
                ->exists();

            if ($exists) {
                // Remove from wishlist
                DB::table('user_wishlist_items')
                    ->where('user_id', $user->id)
                    ->where('cmapi_card_id', $card->cmapi_id)
                    ->delete();
                
                $status = 'removed';
            } else {
                // Add to wishlist
                DB::table('user_wishlist_items')->insert([
                    'user_id' => $user->id,
                    'cmapi_card_id' => $card->cmapi_id,
                    'created_at' => now(),
                ]);
                
                $status = 'added';
            }

            DB::commit();

            $message = $status === 'added'
                ? __('tcg/interactions.wishlist_added')
                : __('tcg/interactions.wishlist_removed');

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => $status,
                    'message' => $message,
                ]);
            }

            // Otherwise redirect back with message
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => __('tcg/interactions.error_generic'),
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', __('tcg/interactions.error_generic'));
        }
    }

    /**
     * Toggle watch on a CMAPI card
     */
    public function toggleWatch(Request $request, string $game, int $cardId)
    {
        $user = Auth::user();
        $card = CmapiCard::findOrFail($cardId);

        DB::beginTransaction();
        try {
            $exists = DB::table('user_watch_items')
                ->where('user_id', $user->id)
                ->where('cmapi_card_id', $card->cmapi_id)
                ->exists();

            if ($exists) {
                // Unwatch
                DB::table('user_watch_items')
                    ->where('user_id', $user->id)
                    ->where('cmapi_card_id', $card->cmapi_id)
                    ->delete();
                
                $status = 'unwatched';
            } else {
                // Watch
                DB::table('user_watch_items')->insert([
                    'user_id' => $user->id,
                    'cmapi_card_id' => $card->cmapi_id,
                    'created_at' => now(),
                ]);
                
                $status = 'watched';
            }

            DB::commit();

            $message = $status === 'watched'
                ? __('tcg/interactions.watch_watched')
                : __('tcg/interactions.watch_unwatched');

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => $status,
                    'message' => $message,
                ]);
            }

            // Otherwise redirect back with message
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => __('tcg/interactions.error_generic'),
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', __('tcg/interactions.error_generic'));
        }
    }
}
