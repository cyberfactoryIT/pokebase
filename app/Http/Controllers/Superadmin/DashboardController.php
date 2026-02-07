<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnforcesSuperAdmin;
use App\Models\User;
use App\Models\Organization;
use App\Models\TcgcsvProduct;
use App\Models\TcgcsvGroup;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxSet;
use App\Models\Cmapi\CmapiCard;
use App\Models\Cmapi\CmapiSet;
use App\Models\RapidapiEpisode;
use App\Models\Article;
use App\Models\Invoice;
use App\Models\Game;
use App\Models\UserCollection;
use App\Models\DeckCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{

    /**
     * Display the superadmin dashboard
     */
    public function index(Request $request): View
    {
        // Check superadmin role with team context
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if (!$user->hasRole('superadmin')) {
            abort(403, 'Unauthorized. SuperAdmin access required.');
        }
        
        // System-wide statistics
        $stats = [
            'total_users' => User::count(),
            'total_organizations' => Organization::count(),
            'total_games' => Game::count(),
            'total_cards' => TcgcsvProduct::count(),
            'total_expansions' => TcgcsvGroup::count(),
            'total_articles' => Article::count(),
        ];

        // Mapping statistics
        $mappingStats = [
            'tcgcsv_groups' => TcgcsvGroup::count(),
            'rapidapi_episodes' => RapidapiEpisode::count(),
            'mapped_groups' => TcgcsvGroup::whereNotNull('rapidapi_episode_id')->count(),
            'unmapped_groups' => TcgcsvGroup::whereNull('rapidapi_episode_id')->count(),
        ];
        
        $mappingStats['mapping_percentage'] = $mappingStats['tcgcsv_groups'] > 0 
            ? round(($mappingStats['mapped_groups'] / $mappingStats['tcgcsv_groups']) * 100, 1)
            : 0;
        
        // Unmapped collection/deck cards statistics
        $collectionProductIds = UserCollection::distinct('product_id')->pluck('product_id');
        $deckProductIds = DeckCard::distinct('product_id')->pluck('product_id');
        $allProductIds = $collectionProductIds->merge($deckProductIds)->unique();
        
        $unmappedCardsStats = [
            'total_in_collections' => $collectionProductIds->count(),
            'total_in_decks' => $deckProductIds->count(),
            'total_unique' => $allProductIds->count(),
            'unmapped_count' => TcgcsvProduct::whereIn('product_id', $allProductIds)
                ->whereNull('cardmarket_product_id')
                ->count(),
        ];

        // Recent users (last 10)
        $recentUsers = User::with('organization')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Revenue statistics (last 30 days)
        $revenueStats = [
            'last_30_days' => Invoice::where('created_at', '>=', now()->subDays(30))
                ->sum('total_cents') / 100,
            'last_month' => Invoice::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('total_cents') / 100,
            'this_year' => Invoice::whereYear('created_at', now()->year)
                ->sum('total_cents') / 100,
        ];

        // Active subscriptions (organizations with a pricing plan and not cancelled)
        $activeSubscriptions = Organization::whereNotNull('pricing_plan_id')
            ->where(function($q) {
                $q->where('subscription_cancelled', false)
                  ->orWhereNull('subscription_cancelled');
            })
            ->count();

        // Games breakdown - count based on catalog_backend
        $gameStats = Game::with('articles')->get()->map(function($game) {
            $cardsCount = 0;
            $setsCount = 0;
            
            switch ($game->catalog_backend) {
                case 'tcgdex':
                    $cardsCount = TcgdxCard::where('game_id', $game->id)->count();
                    $setsCount = TcgdxSet::where('game_id', $game->id)->count();
                    break;
                    
                case 'cmapi':
                    $cardsCount = CmapiCard::where('game_id', $game->id)->count();
                    $setsCount = CmapiSet::where('game_id', $game->id)->count();
                    break;
                    
                case 'tcgcsv':
                default:
                    $cardsCount = TcgcsvProduct::where('category_id', $game->tcgcsv_category_id)->count();
                    $setsCount = TcgcsvGroup::where('category_id', $game->tcgcsv_category_id)->count();
                    break;
            }
            
            return [
                'id' => $game->id,
                'name' => $game->name,
                'code' => $game->code,
                'catalog_backend' => $game->catalog_backend,
                'articles_count' => $game->articles->count(),
                'cards_count' => $cardsCount,
                'sets_count' => $setsCount,
            ];
        });

        // User engagement stats (last 30 days)
        $engagementStats = [
            'active_users' => DB::table('sessions')
                ->where('last_activity', '>=', now()->subDays(30)->timestamp)
                ->distinct('user_id')
                ->whereNotNull('user_id')
                ->count('user_id'),
            'new_users_30d' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'collections_created_30d' => UserCollection::where('created_at', '>=', now()->subDays(30))->count(),
            'total_collections' => UserCollection::count(),
        ];

        // Trial statistics
        $trialStats = [
            'active_trials' => Organization::whereNotNull('trial_plan_id')
                ->where('trial_expires_at', '>', now())
                ->count(),
            'expired_trials' => Organization::whereNotNull('trial_plan_id')
                ->where('trial_expires_at', '<=', now())
                ->count(),
            'converted_from_trial' => Organization::whereNotNull('trial_plan_id')
                ->whereNotNull('pricing_plan_id')
                ->count(),
        ];

        return view('superadmin.dashboard', compact(
            'stats',
            'mappingStats',
            'unmappedCardsStats',
            'recentUsers',
            'revenueStats',
            'activeSubscriptions',
            'gameStats',
            'engagementStats',
            'trialStats'
        ));
    }
    
    /**
     * Refresh cached prices for all cards in collections and decks
     */
    public function refreshPrices(Request $request): RedirectResponse
    {
        // Check superadmin role
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if (!$user->hasRole('superadmin')) {
            abort(403, 'Unauthorized. SuperAdmin access required.');
        }
        
        try {
            // Run the command
            Artisan::call('prices:refresh-cache', ['--force' => true]);
            
            $output = Artisan::output();
            
            return redirect()->route('superadmin.dashboard')
                ->with('success', 'Prices refreshed successfully! ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Error refreshing prices: ' . $e->getMessage());
        }
    }
}
