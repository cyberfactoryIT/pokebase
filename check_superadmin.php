<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'superadmin@example.com')->first();

if (!$user) {
    echo "❌ User superadmin@example.com not found!\n";
    exit(1);
}

echo "✅ User found: {$user->name} (ID: {$user->id})\n";
echo "   Organization ID: {$user->organization_id}\n\n";

// Check roles in database
$roleAssignments = DB::table('model_has_roles')
    ->where('model_id', $user->id)
    ->where('model_type', 'App\Models\User')
    ->get();

echo "Role assignments in model_has_roles:\n";
foreach ($roleAssignments as $assignment) {
    $role = DB::table('roles')->where('id', $assignment->role_id)->first();
    echo "  - Role ID: {$assignment->role_id}, Name: {$role->name}, Org ID in pivot: {$assignment->organization_id}, Org ID in role: " . ($role->organization_id ?? 'NULL') . "\n";
}

// Test with Spatie
echo "\nTesting hasRole() with different team contexts:\n";

// Without team context
app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(null);
$user->load('roles'); // Force reload
echo "  - Team NULL: " . ($user->hasRole('superadmin') ? '✅ YES' : '❌ NO') . "\n";

// With user's org
app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->organization_id);
$user->load('roles'); // Force reload
echo "  - Team {$user->organization_id}: " . ($user->hasRole('superadmin') ? '✅ YES' : '❌ NO') . "\n";

echo "\nDone!\n";
