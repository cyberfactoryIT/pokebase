<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportDashboardController extends Controller
{
    public function index()
    {
        // TCGCSV Import Logs
        $tcgcsvRuns = DB::table('tcgcsv_import_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Cardmarket Import Runs
        $cardmarketRuns = DB::table('cardmarket_import_runs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // RapidAPI Sync Logs
        $rapidapiRuns = DB::table('rapidapi_sync_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // TCGdex Import Runs
        $tcgdexRuns = DB::table('tcgdx_import_runs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($run) {
                // Decode stats JSON to extract sets/cards counts
                $stats = json_decode($run->stats, true) ?? [];
                $run->sets_new = $stats['sets']['new'] ?? 0;
                $run->sets_updated = $stats['sets']['updated'] ?? 0;
                $run->cards_new = $stats['cards']['new'] ?? 0;
                $run->cards_updated = $stats['cards']['updated'] ?? 0;
                return $run;
            });

        // Latest stats for each system
        $stats = [
            'tcgcsv' => [
                'last_run' => $tcgcsvRuns->first(),
                'total_runs' => DB::table('tcgcsv_import_logs')->count(),
                'success_rate' => $this->calculateSuccessRate('tcgcsv_import_logs'),
            ],
            'cardmarket' => [
                'last_run' => $cardmarketRuns->first(),
                'total_runs' => DB::table('cardmarket_import_runs')->count(),
                'success_rate' => $this->calculateSuccessRate('cardmarket_import_runs', 'status'),
            ],
            'rapidapi' => [
                'last_run' => $rapidapiRuns->first(),
                'total_runs' => DB::table('rapidapi_sync_logs')->count(),
                'success_rate' => $this->calculateSuccessRate('rapidapi_sync_logs', 'status'),
            ],
            'tcgdex' => [
                'last_run' => $tcgdexRuns->first(),
                'total_runs' => DB::table('tcgdx_import_runs')->count(),
                'success_rate' => $this->calculateSuccessRate('tcgdx_import_runs', 'status'),
            ],
        ];

        return view('admin.imports.dashboard', compact(
            'tcgcsvRuns',
            'cardmarketRuns',
            'rapidapiRuns',
            'tcgdexRuns',
            'stats'
        ));
    }

    private function calculateSuccessRate(string $table, ?string $statusColumn = null): float
    {
        $total = DB::table($table)->count();
        
        if ($total === 0) {
            return 0;
        }

        if ($statusColumn) {
            $successful = DB::table($table)
                ->where($statusColumn, 'success')
                ->count();
        } else {
            // For tcgcsv_import_logs, use status column
            $successful = DB::table($table)
                ->where('status', 'completed')
                ->count();
        }

        return round(($successful / $total) * 100, 1);
    }
}
