<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVipReward extends Model
{
    use HasFactory;

    /**
     * Atributos atribuíveis em massa.
     */
    protected $fillable = [
        'user_id',
        'vip_reward_id',
        'amount_received',
        'spins_received',
        'status',
        'claimed_at',
    ];

    /**
     * Tipos de dados para casting.
     */
    protected $casts = [
        'amount_received' => 'decimal:2',
        'spins_received' => 'integer',
        'claimed_at' => 'datetime',
    ];

    /**
     * Relação com o usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com a recompensa VIP.
     */
    public function vipReward()
    {
        return $this->belongsTo(VipReward::class);
    }

    /**
     * Verifica se o resgate foi completado.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica se o resgate está pendente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Marca o resgate como completado.
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'claimed_at' => now(),
        ]);
    }
} 