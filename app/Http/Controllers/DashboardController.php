<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvProduct;
use App\Models\TcgcsvGroup;
use App\Models\Deck;
use App\Models\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard
     */
    public function index(Request $request): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no game selected, show empty state
        if (!$currentGame) {
            return view('dashboard', [
                'cardsCount' => 0,
                'expansionsCount' => 0,
                'userDecksCount' => 0,
                'userCollectionCount' => 0,
                'uniqueCardsCount' => 0,
                'currentGame' => null,
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
        
        return view('dashboard', [
            'cardsCount' => $cardsCount,
            'expansionsCount' => $expansionsCount,
            'userDecksCount' => $userDecksCount,
            'userCollectionCount' => $userCollectionCount,
            'uniqueCardsCount' => $uniqueCardsCount,
        ]);
    }
}
