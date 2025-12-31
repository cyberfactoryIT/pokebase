<?php

namespace App\Http\Controllers;

use App\Models\Tcgdx\TcgdxCard;
use Illuminate\View\View;

class TcgdxCardController extends Controller
{
    /**
     * Show card detail page (Scrydex-like layout)
     */
    public function show(string $cardId): View
    {
        $card = TcgdxCard::where('tcgdex_id', $cardId)
            ->with(['set'])
            ->firstOrFail();

        return view('tcgdx.cards.show', compact('card'));
    }
}
