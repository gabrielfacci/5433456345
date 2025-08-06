<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionProcessControl extends Model
{
    protected $table = 'distribution_process_control';

    protected $fillable = [
        'last_execution',
        'next_execution',
        'is_processing',
        'current_mode',
        'current_total',
        'current_target',
        'current_rtp',
        'status',
        'last_error'
    ];

    protected $casts = [
        'last_execution' => 'datetime',
        'next_execution' => 'datetime',
        'is_processing' => 'boolean',
        'current_total' => 'decimal:2',
        'current_target' => 'decimal:2',
        'current_rtp' => 'integer'
    ];

    public static function getCurrentProcess()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'last_execution' => now(),
                'next_execution' => now()->addSeconds(10),
                'is_processing' => false,
                'current_mode' => 'arrecadacao',
                'current_total' => 0,
                'current_target' => 0,
                'current_rtp' => 30,
                'status' => 'idle'
            ]
        );
    }

    public function canProcess(): bool
    {
        if ($this->is_processing) {
            // Se está processando há mais de 1 minuto, considera travado
            if ($this->last_execution->diffInMinutes(now()) > 1) {
                $this->update([
                    'is_processing' => false,
                    'status' => 'reset_after_timeout',
                    'last_error' => 'Processo anterior travado e resetado'
                ]);
                return true;
            }
            return false;
        }

        if ($this->next_execution && $this->next_execution->isFuture()) {
            return false;
        }

        return true;
    }

    public function startProcessing()
    {
        $this->update([
            'is_processing' => true,
            'last_execution' => now(),
            'next_execution' => now()->addSeconds(10),
            'status' => 'processing'
        ]);
    }

    public function finishProcessing($success = true, $error = null)
    {
        $this->update([
            'is_processing' => false,
            'status' => $success ? 'completed' : 'error',
            'last_error' => $error
        ]);
    }
} 