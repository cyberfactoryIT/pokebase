<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingTourController extends Controller
{
    /**
     * Mark the onboarding tour as completed for the authenticated user.
     */
    public function completed(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->onboarding_tour_completed_at) {
            $user->onboarding_tour_completed_at = now();
            $user->save();
        }

        return response()->json([
            'success' => true,
            'status' => 'completed',
        ]);
    }

    /**
     * Mark the onboarding tour as skipped for the authenticated user.
     */
    public function skipped(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->onboarding_tour_skipped_at) {
            $user->onboarding_tour_skipped_at = now();
            $user->save();
        }

        return response()->json([
            'success' => true,
            'status' => 'skipped',
        ]);
    }

    /**
     * Optional: reset onboarding state for the authenticated user.
     */
    public function reset(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $user->onboarding_tour_completed_at = null;
        $user->onboarding_tour_skipped_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'status' => 'reset',
        ]);
    }
}

