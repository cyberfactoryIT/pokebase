<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CardMarketMappingComparisonController extends Controller
{
    /**
     * Display CardMarket mapping comparison between TCGCSV and TCGdex
     */
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all'); // all, conflicts, tcgcsv_only, tcgdex_only, both, neither
        
        // Get products that have TCGdex mapping
        $query = TcgcsvProduct::whereNotNull('tcgdex_card_id')
            ->with(['group:group_id,name,abbreviation', 'tcgdxCard:tcgdex_id,name,cardmarket_product_id']);
        
        // Apply filters
        switch ($filter) {
            case 'conflicts':
                // Both have CardMarket IDs but they're different
                $query->whereNotNull('cardmarket_product_id')
                    ->whereHas('tcgdxCard', function($q) {
                        $q->whereNotNull('cardmarket_product_id')
                          ->whereRaw('tcgcsv_products.cardmarket_product_id != tcgdx_cards.cardmarket_product_id');
                    });
                break;
                
            case 'tcgcsv_only':
                // Only TCGCSV has CardMarket ID
                $query->whereNotNull('cardmarket_product_id')
                    ->whereHas('tcgdxCard', function($q) {
                        $q->whereNull('cardmarket_product_id');
                    });
                break;
                
            case 'tcgdex_only':
                // Only TCGdex has CardMarket ID
                $query->whereNull('cardmarket_product_id')
                    ->whereHas('tcgdxCard', function($q) {
                        $q->whereNotNull('cardmarket_product_id');
                    });
                break;
                
            case 'both':
                // Both have same CardMarket ID
                $query->whereNotNull('cardmarket_product_id')
                    ->whereHas('tcgdxCard', function($q) {
                        $q->whereNotNull('cardmarket_product_id')
                          ->whereRaw('tcgcsv_products.cardmarket_product_id = tcgdx_cards.cardmarket_product_id');
                    });
                break;
                
            case 'neither':
                // Neither has CardMarket ID
                $query->whereNull('cardmarket_product_id')
                    ->whereHas('tcgdxCard', function($q) {
                        $q->whereNull('cardmarket_product_id');
                    });
                break;
        }
        
        // Add search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('card_number', 'like', "%{$search}%");
            });
        }
        
        $products = $query->orderBy('group_id', 'desc')
            ->orderBy('card_number', 'asc')
            ->paginate(50);
        
        // Calculate statistics
        $stats = [
            'total_with_tcgdex' => TcgcsvProduct::whereNotNull('tcgdex_card_id')->count(),
            'conflicts' => TcgcsvProduct::whereNotNull('tcgdex_card_id')
                ->whereNotNull('cardmarket_product_id')
                ->whereHas('tcgdxCard', function($q) {
                    $q->whereNotNull('cardmarket_product_id')
                      ->whereRaw('tcgcsv_products.cardmarket_product_id != tcgdx_cards.cardmarket_product_id');
                })
                ->count(),
            'tcgcsv_only' => TcgcsvProduct::whereNotNull('tcgdex_card_id')
                ->whereNotNull('cardmarket_product_id')
                ->whereHas('tcgdxCard', function($q) {
                    $q->whereNull('cardmarket_product_id');
                })
                ->count(),
            'tcgdex_only' => TcgcsvProduct::whereNotNull('tcgdex_card_id')
                ->whereNull('cardmarket_product_id')
                ->whereHas('tcgdxCard', function($q) {
                    $q->whereNotNull('cardmarket_product_id');
                })
                ->count(),
            'both_same' => TcgcsvProduct::whereNotNull('tcgdex_card_id')
                ->whereNotNull('cardmarket_product_id')
                ->whereHas('tcgdxCard', function($q) {
                    $q->whereNotNull('cardmarket_product_id')
                      ->whereRaw('tcgcsv_products.cardmarket_product_id = tcgdx_cards.cardmarket_product_id');
                })
                ->count(),
            'both_null' => TcgcsvProduct::whereNotNull('tcgdex_card_id')
                ->whereNull('cardmarket_product_id')
                ->whereHas('tcgdxCard', function($q) {
                    $q->whereNull('cardmarket_product_id');
                })
                ->count(),
        ];
        
        return view('admin.cardmarket-comparison.index', compact('products', 'stats', 'filter'));
    }
}
