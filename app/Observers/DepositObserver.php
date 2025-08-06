<?php

namespace App\Observers;

use App\Models\CrmDepositUser;
use App\Models\Deposit;

class DepositObserver
{
    public function saved(Deposit $deposit): void
    {
        // Processa apenas quando o depósito for confirmado (status == 1) e tenha mudado para confirmado
        $statusChangedToConfirmed = $deposit->wasRecentlyCreated
            ? $deposit->status == 1
            : ($deposit->getOriginal('status') != 1 && $deposit->status == 1);

        if (! $statusChangedToConfirmed) {
            return;
        }

        $user = $deposit->user;

        $crm = CrmDepositUser::firstOrNew(['user_id' => $user->id]);
        $crm->name = $user->name;
        $crm->email = $user->email;
        $crm->phone = $user->phone;

        // Increment counts
        if ($crm->exists) {
            $crm->deposits_count += 1;
            $crm->deposits_total += $deposit->amount;
        } else {
            $crm->deposits_count = 1;
            $crm->deposits_total = $deposit->amount;
            $crm->first_deposit_at = now();
        }

        $crm->last_deposit_at = now();
        $crm->save();
    }
} 