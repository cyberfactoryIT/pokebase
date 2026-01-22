<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvProduct;
use App\Models\TcgcsvGroup;
use App\Models\Deck;
use App\Models\UserCollection;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Check if user is superadmin and redirect to superadmin dashboard
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if ($user->hasRole('superadmin')) {
            return redirect()->route('superadmin.dashboard');
        }
        
        $currentGame = $request->attributes->get('currentGame');
        
        // If no game selected, show empty state
        if (!$currentGame) {
            return view('dashboard', [
                'cardsCount' => 0,
                'expansionsCount' => 0,
                'userDecksCount' => 0,
                'userCollectionCount' => 0,
                'uniqueCardsCount' => 0,
                'collectionValue' => 0,
                'currentGame' => null,
                'articles' => collect(),
                'articleCategories' => collect(),
                'userLocale' => app()->getLocale(),
                'recentAdditions' => collect(),
                'topCards' => collect(),
                'userExpansions' => collect(),
            ]);
        }
        
        // Get counts filtered by current game
        $cardsCount = TcgcsvProduct::where('game_id', $currentGame->id)->count();
        $expansionsCount = TcgcsvGroup::where('game_id', $currentGame->id)->count();
        $userDecksCount = Deck::where('user_id', Auth::id())
            ->where('game_id', $currentGame->id)
            ->count();
        
        // Get user collection stats for current game
        $userCollectionCount = UserCollection::where('user_id', Auth::id())
            ->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            })
            ->sum('quantity');
            
        $uniqueCardsCount = UserCollection::where('user_id', Auth::id())
            ->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            })
            ->count();
        
        // Get current locale (from session or user preference)
        $userLocale = app()->getLocale();
        
        // Get articles for current game
        $articlesQuery = Article::published()
            ->where('game_id', $currentGame->id);
        
        // Filter by category if specified
        if ($request->has('article_category') && $request->article_category) {
            $articlesQuery->where('category', $request->article_category);
        }
        
        $articles = $articlesQuery
            ->orderByRaw('sort_order is null, sort_order asc')
            ->orderByDesc('published_at')
            ->limit(9)
            ->get();
        
        // Get available categories for this game
        $articleCategories = Article::published()
            ->where('game_id', $currentGame->id)
            ->distinct()
            ->pluck('category')
            ->sort();
        
        // Get recent additions (last 6 cards added to collection)
        $recentAdditions = UserCollection::where('user_id', Auth::id())
            ->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            })
            ->with(['card.group', 'card.prices'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
        
        // Get top 5 most valuable cards in collection
        $topCards = UserCollection::where('user_id', Auth::id())
            ->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            })
            ->with(['card.group', 'card.prices'])
            ->get()
            ->sortByDesc(function($item) {
                $latestPrice = $item->card->prices->first();
                return $latestPrice?->market_price ?? 0;
            })
            ->take(5);
        
        // Calculate total collection value
        $collectionValue = UserCollection::where('user_id', Auth::id())
            ->whereHas('card', function($q) use ($currentGame) {
                $q->where('game_id', $currentGame->id);
            })
            ->with('card.prices')
            ->get()
            ->sum(function($item) {
                $latestPrice = $item->card->prices->first();
                $price = $latestPrice?->market_price ?? 0;
                return $price * $item->quantity;
            });
        
        // Get expansions for missing cards dropdown
        // Get unique group_ids from user's collection for this game
        $userGroupIds = UserCollection::where('user_id', Auth::id())
            ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
            ->where('tcgcsv_products.game_id', $currentGame->id)
            ->distinct()
            ->pluck('tcgcsv_products.group_id');
        
        $userExpansions = TcgcsvGroup::where('game_id', $currentGame->id)
            ->whereIn('group_id', $userGroupIds)
            ->orderBy('published_on', 'desc')
            ->limit(10)
            ->get();
        
        // Featured expansions for carousel
        $featuredExpansions = TcgcsvGroup::where('game_id', $currentGame->id)
            ->where('show_in_carousel', true)
            ->orderBy('published_on', 'desc')
            ->get();
        
        return view('dashboard', [
            'cardsCount' => $cardsCount,
            'expansionsCount' => $expansionsCount,
            'userDecksCount' => $userDecksCount,
            'userCollectionCount' => $userCollectionCount,
            'uniqueCardsCount' => $uniqueCardsCount,
            'collectionValue' => $collectionValue,
            'articles' => $articles,
            'articleCategories' => $articleCategories,
            'userLocale' => $userLocale,
            'recentAdditions' => $recentAdditions,
            'topCards' => $topCards,
            'userExpansions' => $userExpansions,
            'featuredExpansions' => $featuredExpansions,
            'currentGame' => $currentGame,
        ]);
    }
}
