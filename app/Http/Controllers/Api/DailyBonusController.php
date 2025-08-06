<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyBonusConfig;
use App\Models\DailyBonusClaim;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyBonusController extends Controller
{
    /**
     * Verifica o status do bônus diário do usuário
     */
    public function check(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Usuário não autenticado.',
                'can_claim' => false
            ], 401);
        }

        // Verificar se o sistema está ativo
        $config = DailyBonusConfig::first();
        if (!$config || !$config->is_active) {
            return response()->json([
                'can_claim' => false,
                'message' => 'Sistema de bônus diário não está disponível.',
                'is_active' => false
            ]);
        }

        // Verificar último resgate do usuário
        $lastClaim = DailyBonusClaim::where('user_id', $user->id)
            ->orderBy('claimed_at', 'desc')
            ->first();

        $canClaim = true;
        $nextClaimTime = null;
        $timeRemaining = null;

        if ($lastClaim) {
            $nextClaimTime = Carbon::parse($lastClaim->claimed_at)->addHours($config->cycle_hours);
            $canClaim = Carbon::now()->gte($nextClaimTime);
            
            if (!$canClaim) {
                $timeRemaining = Carbon::now()->diffInSeconds($nextClaimTime);
            }
        }

        return response()->json([
            'can_claim' => $canClaim,
            'message' => $canClaim 
                ? 'Você pode resgatar seu bônus diário!' 
                : 'Aguarde para resgatar o próximo bônus.',
            'bonus_value' => $config->bonus_value,
            'cycle_hours' => $config->cycle_hours,
            'next_claim_time' => $nextClaimTime?->toISOString(),
            'time_remaining_seconds' => $timeRemaining,
            'last_claim' => $lastClaim?->claimed_at,
            'is_active' => true
        ]);
    }

    /**
     * Resgata o bônus diário
     */
    public function claim(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Usuário não autenticado.',
                'success' => false
            ], 401);
        }

        // Verificar se o sistema está ativo
        $config = DailyBonusConfig::first();
        if (!$config || !$config->is_active) {
            return response()->json([
                'error' => 'Sistema de bônus diário não está disponível.',
                'success' => false
            ], 400);
        }

        // Verificar se pode resgatar
        $lastClaim = DailyBonusClaim::where('user_id', $user->id)
            ->orderBy('claimed_at', 'desc')
            ->first();

        if ($lastClaim) {
            $nextClaimTime = Carbon::parse($lastClaim->claimed_at)->addHours($config->cycle_hours);
            if (Carbon::now()->lt($nextClaimTime)) {
                $timeRemaining = Carbon::now()->diffInSeconds($nextClaimTime);
                return response()->json([
                    'error' => 'Você ainda não pode resgatar o bônus. Aguarde mais tempo.',
                    'success' => false,
                    'time_remaining_seconds' => $timeRemaining,
                    'next_claim_time' => $nextClaimTime->toISOString()
                ], 400);
            }
        }

        // Buscar carteira do usuário
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            return response()->json([
                'error' => 'Carteira não encontrada.',
                'success' => false
            ], 400);
        }

        try {
            // Creditar o bônus na carteira
            $bonusField = $config->bonus_type; // 'balance_bonus' ou 'balance_withdrawal'
            $wallet->increment($bonusField, $config->bonus_value);

            // Registrar o resgate
            DailyBonusClaim::create([
                'user_id' => $user->id,
                'claimed_at' => Carbon::now(),
            ]);

            // Log para auditoria
            \Log::info('Daily Bonus Claimed', [
                'user_id' => $user->id,
                'bonus_value' => $config->bonus_value,
                'bonus_type' => $config->bonus_type,
                'claimed_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bônus resgatado com sucesso!',
                'bonus_value' => $config->bonus_value,
                'bonus_type' => $config->bonus_type,
                'next_claim_hours' => $config->cycle_hours
            ]);

        } catch (\Exception $e) {
            \Log::error('Daily Bonus Claim Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erro interno. Tente novamente.',
                'success' => false
            ], 500);
        }
    }

    /**
     * Histórico de resgates do usuário
     */
    public function history(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Usuário não autenticado.'
            ], 401);
        }

        $claims = DailyBonusClaim::where('user_id', $user->id)
            ->orderBy('claimed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($claim) {
                return [
                    'claimed_at' => $claim->claimed_at,
                    'claimed_at_formatted' => Carbon::parse($claim->claimed_at)->format('d/m/Y H:i:s')
                ];
            });

        return response()->json([
            'claims' => $claims,
            'total_claims' => DailyBonusClaim::where('user_id', $user->id)->count()
        ]);
    }
} 