<?php
namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllInvoicesController
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasRole('superadmin')) abort(403);
        $query = \App\Models\Invoice::with('organization')->orderByDesc('issued_at');
        if (config('organizations.enabled') && $request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        $invoices = $query->paginate(50);
        return view('admin.allinvoices.index', compact('invoices'));
    }
}
