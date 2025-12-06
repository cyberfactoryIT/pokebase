<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Autorizzazione esplicita nei singoli metodi

    public function index(Request $request)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager','team','auditor'])) abort(403);
        $orgId = config('organizations.enabled') ? auth()->user()->organization_id : null;
        $projects = Project::forOrg($orgId)
            ->when($request->input('q'), fn($q, $qv) => $q->where(fn($x) => $x
                ->where('name', 'like', "%$qv%")
                ->orWhere('code', 'like', "%$qv%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('projects.index', compact('projects'));
    }

   
    public function create()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager'])) abort(403);
    $users = User::where('organization_id', config('organizations.enabled') ? auth()->user()->organization_id : null)
            ->orderBy('name')->get(['id','name','email']);
        return view('projects.create', compact('users'));
    }

    public function store(StoreProjectRequest $request)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager'])) abort(403);
        DB::transaction(function() use($request){
            Project::create(array_merge(
                $request->validated(),
                ['organization_id' => config('organizations.enabled') ? auth()->user()->organization_id : null]
            ));
        });
        return redirect()->route('projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager'])) abort(403);
    $users = User::where('organization_id', config('organizations.enabled') ? auth()->user()->organization_id : null)
            ->orderBy('name')->get(['id','name','email']);
        return view('projects.edit', compact('project','users'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager'])) abort(403);
        $project->update($request->validated());
        return redirect()->route('projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager'])) abort(403);
        $project->delete();
        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }

    public function show(Project $project)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? auth()->user()->organization_id : null
        );
        if (!auth()->user()->hasAnyRole(['admin','manager','team','auditor'])) abort(403);
        return view('projects.show', compact('project'));
    }
}
