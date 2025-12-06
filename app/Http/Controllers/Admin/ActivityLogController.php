<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query();
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if (config('organizations.enabled')) {
            $org = $request->input('organization_id');
            if ($org) {
                $query->where('organization_id', $org);
            }
            // Se admin, mostra solo i log della propria organizzazione
            if (optional(auth()->user())->hasRole('admin')) {
                $query->where('organization_id', auth()->user()->organization_id);
            }
        }
    $logs = $query->latest()->paginate(30);
    $isAdmin = optional(auth()->user())->hasRole('admin');
    $types = trans('logmessages.type');
    $organizations = config('organizations.enabled') ? \App\Models\Organization::pluck('name', 'id') : collect();
    return view('admin.activitylog.index', compact('logs', 'organizations', 'types', 'isAdmin'));
    }
}
