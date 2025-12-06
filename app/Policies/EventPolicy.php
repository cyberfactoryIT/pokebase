<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Event;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return $user->can('project.view');
    }

    public function manage(User $user, Event $event): bool
    {
        return $user->can('project.manage');
    }
}
