<?php
namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function before(User $user, $ability)
    {
        return $user->is_admin === true;
    }

    public function viewAny(User $user)
    {
        return $user->is_admin === true;
    }

    public function view(User $user, Faq $faq)
    {
        return $user->is_admin === true;
    }

    public function create(User $user)
    {
        return $user->is_admin === true;
    }

    public function update(User $user, Faq $faq)
    {
        return $user->is_admin === true;
    }

    public function delete(User $user, Faq $faq)
    {
        return $user->is_admin === true;
    }
}
