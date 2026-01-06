<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        \App\Models\ActivityLog::log(
            'create',
            "User baru mendaftar: {$user->name}",
            $user,
            null,
            $user->toArray()
        );
    }

    public function updated(User $user): void
    {
        // Optional: reduce noise by only logging important updates
    }

    public function deleted(User $user): void
    {
        \App\Models\ActivityLog::log(
            'delete',
            "Menghapus user: {$user->name}",
            $user,
            $user->toArray(),
            null
        );
    }
}
