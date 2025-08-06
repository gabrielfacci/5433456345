<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyBonusConfig extends Model
{
    // Nome da tabela
    protected $table = 'daily_bonus_configs';

    // Colunas que podem ser preenchidas em massa
    protected $fillable = [
        'bonus_value',
        'cycle_hours',
        'is_active',
        'bonus_type',
    ];

    // Tipos de dados
    protected $casts = [
        'bonus_value' => 'decimal:2',
        'cycle_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    // Se estiver usando timestamps (created_at, updated_at),
    // deixe como está; caso contrário, defina $timestamps = false;
    // public $timestamps = false;
}
