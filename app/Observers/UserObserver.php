<?php

namespace App\Observers;

use App\Models\CrmSignup;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        CrmSignup::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        );
    }
} 