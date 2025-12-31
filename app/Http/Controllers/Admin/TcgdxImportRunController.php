<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tcgdx\TcgdxImportRun;
use Illuminate\View\View;

class TcgdxImportRunController extends Controller
{
    public function index(): View
    {
        $runs = TcgdxImportRun::query()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.tcgdx.import-runs', compact('runs'));
    }
}
