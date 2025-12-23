<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvProduct;
use App\Models\TcgcsvGroup;
use Illuminate\Http\Request;
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
        
        return view('dashboard', [
            'cardsCount' => $cardsCount,
            'expansionsCount' => $expansionsCount,
        ]);
    }
}
