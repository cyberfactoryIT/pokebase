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
    public function index(): View
    {
        // Get simple counts for display
        $cardsCount = TcgcsvProduct::count();
        $expansionsCount = TcgcsvGroup::count();
        $userDecksCount = Deck::where('user_id', Auth::id())->count();
        
        // Get user collection stats
        $userCollectionCount = UserCollection::where('user_id', Auth::id())->sum('quantity');
        $uniqueCardsCount = UserCollection::where('user_id', Auth::id())->count();
        
        return view('dashboard', [
            'cardsCount' => $cardsCount,
            'expansionsCount' => $expansionsCount,
            'userDecksCount' => $userDecksCount,
            'userCollectionCount' => $userCollectionCount,
            'uniqueCardsCount' => $uniqueCardsCount,
        ]);
    }
}
