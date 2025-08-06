<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmDepositUser extends Model
{
    use HasFactory;

    protected $table = 'crm_deposit_users';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'deposits_count',
        'deposits_total',
        'first_deposit_at',
        'last_deposit_at',
    ];

    protected $casts = [
        'first_deposit_at' => 'datetime',
        'last_deposit_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 