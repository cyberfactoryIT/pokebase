<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnforcesSuperAdmin;
use App\Models\TcgcsvGroup;
use App\Services\RapidapiGroupMappingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RapidapiGroupMappingController extends Controller
{
    protected RapidapiGroupMappingService $mappingService;

    public function __construct(RapidapiGroupMappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    /**
     * Display the mapping console
     */
    public function index(Request $request): View
    {
        // Check superadmin role with team context
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if (!$user->hasRole('superadmin')) {
            abort(403, 'Unauthorized. SuperAdmin access required.');
        }
        
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        $groups = $this->mappingService->listGroups($filter, $search, 25);
        
        // Get available expansions (will be empty collection, used for count)
        $availableExpansions = $this->mappingService->listAvailableRapidapiExpansions();
        
        // Get suggestions for each unmapped group
        $suggestions = [];
        foreach ($groups as $group) {
            if (!$group->rapidapi_episode_id) {
                $suggestion = $this->mappingService->getSuggestedExpansion($group);
                if ($suggestion) {
                    $suggestions[$group->id] = $suggestion;
                }
            }
        }
        
        $statistics = $this->mappingService->getStatistics();

        return view('superadmin.rapidapi-mapping.index', compact(
            'groups',
            'availableExpansions',
            'suggestions',
            'statistics',
            'filter',
            'search'
        ));
    }

    /**
     * Map a TCGCSV group to a RapidAPI episode
     */
    public function map(Request $request, TcgcsvGroup $group): RedirectResponse
    {
        // Check superadmin role with team context
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if (!$user->hasRole('superadmin')) {
            abort(403, 'Unauthorized. SuperAdmin access required.');
        }
        
        $request->validate([
            'rapidapi_episode_id' => 'required|integer|exists:rapidapi_episodes,episode_id',
        ]);

        $result = $this->mappingService->mapGroupToRapidapi(
            $group->id,
            $request->input('rapidapi_episode_id'),
            auth()->id()
        );

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Unmap a TCGCSV group from its RapidAPI episode
     */
    public function unmap(TcgcsvGroup $group): RedirectResponse
    {
        // Check superadmin role with team context
        $user = auth()->user();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
        
        if (!$user->hasRole('superadmin')) {
            abort(403, 'Unauthorized. SuperAdmin access required.');
        }
        
        $result = $this->mappingService->unmapGroup($group->id, auth()->id());

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
