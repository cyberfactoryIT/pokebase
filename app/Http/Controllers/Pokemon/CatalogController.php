<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Models\Tcgdx\TcgdxSet;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\TcgcsvGroup;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display list of sets
     */
    public function sets(Request $request): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }
        
        // Pokemon routes ALWAYS use TCGDEX when configured
        if (config('catalog.backend') === 'tcgdex') {
            $sets = TcgdxSet::where('game_id', $currentGame->id)
                ->orderByDesc('release_date')
                ->paginate(24);
            
            return view('pokemon.catalog.sets-tcgdex', [
                'sets' => $sets,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
            ]);
        }
        
        // Use TCGCSV data (default)
        $sets = TcgcsvGroup::where('game_id', $currentGame->id)
            ->orderByDesc('published_on')
            ->paginate(24);
        
        return view('pokemon.catalog.sets', [
            'sets' => $sets,
            'currentGame' => $currentGame,
            'backend' => 'tcgcsv',
        ]);
    }
    
    /**
     * Display cards in a set
     */
    public function setCards(Request $request, string $setId): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }
        
        // Pokemon routes ALWAYS use TCGDEX when configured
        if (config('catalog.backend') === 'tcgdex') {
            // Find set by tcgdex_id
            $set = TcgdxSet::where('tcgdex_id', $setId)
                ->where('game_id', $currentGame->id)
                ->firstOrFail();
            
            $cards = TcgdxCard::where('set_tcgdx_id', $set->id)
                ->orderBy('local_id')
                ->paginate(50);
            
            return view('pokemon.catalog.set-cards-tcgdex', [
                'set' => $set,
                'cards' => $cards,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
            ]);
        }
        
        // Use TCGCSV data (default)
        $set = TcgcsvGroup::where('group_id', $setId)
            ->where('game_id', $currentGame->id)
            ->firstOrFail();
        
        $cards = TcgcsvProduct::where('group_id', $setId)
            ->where('game_id', $currentGame->id)
            ->orderBy('product_id')
            ->paginate(50);
        
        return view('pokemon.catalog.set-cards', [
            'set' => $set,
            'cards' => $cards,
            'currentGame' => $currentGame,
            'backend' => 'tcgcsv',
        ]);
    }
    
    /**
     * Display single card details
     */
    public function card(Request $request, string $cardId): View
    {
        $currentGame = $request->attributes->get('currentGame');
        
        // If no current game (user not logged in), default to Pokemon
        if (!$currentGame) {
            $currentGame = \App\Models\Game::where('code', 'pokemon')->first();
            if (!$currentGame) {
                abort(404, 'Pokemon game not found');
            }
        }
        
        // Pokemon routes ALWAYS use TCGDEX when configured
        if (config('catalog.backend') === 'tcgdex') {
            $card = TcgdxCard::where('tcgdex_id', $cardId)
                ->whereHas('set', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                })
                ->with('set')
                ->firstOrFail();
            
            return view('pokemon.catalog.card-tcgdex', [
                'card' => $card,
                'currentGame' => $currentGame,
                'backend' => 'tcgdex',
            ]);
        }
        
        // Use TCGCSV data (default)
        $card = TcgcsvProduct::where('product_id', $cardId)
            ->where('game_id', $currentGame->id)
            ->with('group')
            ->firstOrFail();
        
        return view('pokemon.catalog.card', [
            'card' => $card,
            'currentGame' => $currentGame,
            'backend' => 'tcgcsv',
        ]);
    }
}
