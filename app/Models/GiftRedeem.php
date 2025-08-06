<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftRedeem extends Model
{
    use HasFactory;

    protected $table = 'gift_redeem';

    protected $fillable = [
        'gift_id',
        'user_id',
        'amount',
        'spins',
        'code',
        'is_used',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_used' => 'boolean',
    ];

    // RELACIONAMENTOS

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
