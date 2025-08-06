<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gift extends Model
{
    use HasFactory;

    protected $table = 'gift';

    protected $fillable = [
        'name',
        'amount',
        'code',
        'spins',
        'is_active',
        'quantity',
        'game_code',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // RELACIONAMENTOS

    public function giftRedeems()
    {
        return $this->hasMany(GiftRedeem::class);
    }
}
