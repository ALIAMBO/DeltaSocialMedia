<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Automatically create a profile when a new user registers.
     */
    public function created(User $user): void
    {
        $user->profile()->create([]);
    }
}
