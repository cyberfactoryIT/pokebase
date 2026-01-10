<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-auth', function () {
    if (!auth()->check()) {
        return response()->json(['logged_in' => false]);
    }
    
    $user = auth()->user();
    
    // Set team context
    app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
    
    return response()->json([
        'logged_in' => true,
        'user_id' => $user->id,
        'user_email' => $user->email,
        'user_name' => $user->name,
        'organization_id' => $user->organization_id,
        'has_superadmin_role' => $user->hasRole('superadmin'),
        'all_roles' => $user->getRoleNames(),
    ]);
})->middleware('web');
