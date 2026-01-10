<?php

namespace App\Services;

use App\Models\RapidapiEpisode;
use App\Models\TcgcsvGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RapidapiGroupMappingService
{
    /**
     * List TCGCSV groups with optional filters
     *
     * @param string|null $filter 'all', 'mapped', 'unmapped'
     * @param string|null $search Search term for group name or abbreviation
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listGroups(?string $filter = 'all', ?string $search = null, int $perPage = 25): LengthAwarePaginator
    {
        $query = TcgcsvGroup::query()
            ->with('rapidapiEpisode:episode_id,name,code,game')
            ->orderBy('published_on', 'desc')
            ->orderBy('name', 'asc');

        // Apply filter
        if ($filter === 'mapped') {
            $query->whereNotNull('rapidapi_episode_id');
        } elseif ($filter === 'unmapped') {
            $query->whereNull('rapidapi_episode_id');
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('abbreviation', 'like', "%{$search}%")
                  ->orWhere('group_id', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->appends([
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    /**
     * Get available RapidAPI episodes not yet mapped to any group
     *
     * @param TcgcsvGroup|null $forGroup If provided, prioritize expansions with matching release date
     * @return Collection
     */
    public function listAvailableRapidapiExpansions(?TcgcsvGroup $forGroup = null): Collection
    {
        // Get episode_ids that are already assigned
        $assignedEpisodeIds = TcgcsvGroup::whereNotNull('rapidapi_episode_id')
            ->pluck('rapidapi_episode_id')
            ->toArray();

        $query = RapidapiEpisode::whereNotIn('episode_id', $assignedEpisodeIds);

        // If we have a group with published_on date, prioritize matching dates
        if ($forGroup && $forGroup->published_on) {
            $publishedDate = $forGroup->published_on->format('Y-m-d');
            
            // Order by date match first, then by release date desc
            $query->orderByRaw("CASE WHEN DATE(released_at) = ? THEN 0 ELSE 1 END", [$publishedDate])
                  ->orderBy('released_at', 'desc')
                  ->orderBy('name', 'asc');
        } else {
            $query->orderBy('released_at', 'desc')
                  ->orderBy('name', 'asc');
        }

        return $query->get(['episode_id', 'name', 'code', 'game', 'released_at']);
    }
    
    /**
     * Get suggested RapidAPI episode for a TCGCSV group based on release date and name similarity
     *
     * @param TcgcsvGroup $group
     * @return RapidapiEpisode|null
     */
    public function getSuggestedExpansion(TcgcsvGroup $group): ?RapidapiEpisode
    {
        if (!$group->published_on) {
            return null;
        }

        // Get available episodes
        $assignedEpisodeIds = TcgcsvGroup::whereNotNull('rapidapi_episode_id')
            ->pluck('rapidapi_episode_id')
            ->toArray();

        $publishedDate = $group->published_on->format('Y-m-d');
        
        // Find episodes released on the same date
        $candidates = RapidapiEpisode::whereNotIn('episode_id', $assignedEpisodeIds)
            ->whereDate('released_at', $publishedDate)
            ->get(['episode_id', 'name', 'code', 'game', 'released_at']);

        // If exactly one match by date, return it as strong suggestion
        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        // If multiple matches by date, try to find best match by name similarity
        if ($candidates->count() > 1) {
            $groupName = strtolower($group->name);
            $bestMatch = null;
            $highestSimilarity = 0;

            foreach ($candidates as $candidate) {
                similar_text($groupName, strtolower($candidate->name), $similarity);
                if ($similarity > $highestSimilarity) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $candidate;
                }
            }

            // Only return if similarity is reasonably high (> 60%)
            if ($highestSimilarity > 60) {
                return $bestMatch;
            }
        }

        return null;
    }

    /**
     * Map a TCGCSV group to a RapidAPI episode
     *
     * @param int $groupId
     * @param int $rapidapiEpisodeId
     * @param int|null $userId
     * @return array ['success' => bool, 'message' => string]
     * @throws \Exception
     */
    public function mapGroupToRapidapi(int $groupId, int $rapidapiEpisodeId, ?int $userId = null): array
    {
        return DB::transaction(function () use ($groupId, $rapidapiEpisodeId, $userId) {
            // Validate group exists
            $group = TcgcsvGroup::lockForUpdate()->find($groupId);
            if (!$group) {
                return ['success' => false, 'message' => __('admin_mappings.errors.group_not_found')];
            }

            // Check if group is already mapped
            if ($group->rapidapi_episode_id !== null) {
                return ['success' => false, 'message' => __('admin_mappings.errors.group_already_mapped')];
            }

            // Validate RapidAPI episode exists
            $episode = RapidapiEpisode::where('episode_id', $rapidapiEpisodeId)->first();
            if (!$episode) {
                return ['success' => false, 'message' => __('admin_mappings.errors.rapidapi_not_found')];
            }

            // Check if this RapidAPI episode is already assigned to another group
            $existingMapping = TcgcsvGroup::where('rapidapi_episode_id', $rapidapiEpisodeId)
                ->where('id', '!=', $group->id)
                ->lockForUpdate()
                ->first();

            if ($existingMapping) {
                return [
                    'success' => false,
                    'message' => __('admin_mappings.errors.rapidapi_already_assigned', [
                        'group' => $existingMapping->name
                    ])
                ];
            }

            // Perform the mapping
            $group->rapidapi_episode_id = $rapidapiEpisodeId;
            
            // Also copy logo_url if group doesn't have one
            if (empty($group->logo_url) && !empty($episode->logo_url)) {
                $group->logo_url = $episode->logo_url;
            }
            
            $group->save();

            return [
                'success' => true,
                'message' => __('admin_mappings.messages.mapped_success', [
                    'group' => $group->name,
                    'episode' => $episode->name
                ])
            ];
        });
    }

    /**
     * Unmap a TCGCSV group from its RapidAPI episode
     *
     * @param int $groupId
     * @param int|null $userId
     * @return array ['success' => bool, 'message' => string]
     */
    public function unmapGroup(int $groupId, ?int $userId = null): array
    {
        return DB::transaction(function () use ($groupId, $userId) {
            $group = TcgcsvGroup::lockForUpdate()->find($groupId);
            
            if (!$group) {
                return ['success' => false, 'message' => __('admin_mappings.errors.group_not_found')];
            }

            if ($group->rapidapi_episode_id === null) {
                return ['success' => false, 'message' => __('admin_mappings.errors.group_not_mapped')];
            }

            $episodeName = $group->rapidapiEpisode->name ?? 'Unknown';
            
            $group->rapidapi_episode_id = null;
            $group->save();

            return [
                'success' => true,
                'message' => __('admin_mappings.messages.unmapped_success', [
                    'group' => $group->name,
                    'episode' => $episodeName
                ])
            ];
        });
    }

    /**
     * Get mapping statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $totalGroups = TcgcsvGroup::count();
        $mappedGroups = TcgcsvGroup::whereNotNull('rapidapi_episode_id')->count();
        $totalEpisodes = RapidapiEpisode::count();
        $usedEpisodes = TcgcsvGroup::whereNotNull('rapidapi_episode_id')->distinct('rapidapi_episode_id')->count('rapidapi_episode_id');

        return [
            'total_groups' => $totalGroups,
            'mapped_groups' => $mappedGroups,
            'unmapped_groups' => $totalGroups - $mappedGroups,
            'mapping_percentage' => $totalGroups > 0 ? round(($mappedGroups / $totalGroups) * 100, 1) : 0,
            'total_episodes' => $totalEpisodes,
            'used_episodes' => $usedEpisodes,
            'available_episodes' => $totalEpisodes - $usedEpisodes,
        ];
    }
}
