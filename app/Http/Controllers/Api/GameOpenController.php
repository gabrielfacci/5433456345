<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameOpenConfig;
use App\Models\Deposit;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameOpenController extends Controller
{
    public function checkDailyDeposit(Request $request)
    {
        // Verifica se o usuário está autenticado via 'auth:api' ou 'auth:jwt'
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Usuário não autenticado.',
                'can_open' => false
            ], 401);
        }

        // Carrega a config de abertura de jogos
        $config = GameOpenConfig::first();
        if (!$config) {
            return response()->json([
                'error' => 'Configuração não encontrada.',
                'can_open' => false
            ], 500);
        }

        // Buscar carteira para informações de saldo
        $wallet = Wallet::where('user_id', $user->id)->first();
        $hasRealBalance = $wallet ? $wallet->total_balance_without_bonus > 0 : false;
        $hasAnyBalance = $wallet ? $wallet->total_balance > 0 : false;
        $hasOnlyBonus = $hasAnyBalance && !$hasRealBalance;

        // Se não exigir depósito hoje, pode abrir
        if (!$config->requires_deposit_today) {
            return response()->json([
                'can_open' => true,
                'message'  => 'Acesso liberado. Pode jogar com qualquer saldo.',
                'requires_deposit' => false,
                'allows_no_balance' => true,
                'balance_info' => [
                    'has_real_balance' => $hasRealBalance,
                    'has_any_balance' => $hasAnyBalance,
                    'has_only_bonus' => $hasOnlyBonus
                ]
            ]);
        }

        // Se tem saldo real, pode jogar sem precisar depositar hoje
        if ($hasRealBalance) {
            \Log::info('GameOpen: Acesso liberado - tem saldo real', [
                'user_id' => $user->id,
                'has_real_balance' => true,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'can_open' => true,
                'message' => 'Acesso liberado. Você tem saldo real.',
                'requires_deposit' => false,
                'balance_info' => [
                    'has_real_balance' => $hasRealBalance,
                    'has_any_balance' => $hasAnyBalance,
                    'has_only_bonus' => $hasOnlyBonus
                ]
            ]);
        }
        
        // Se não tem saldo real (sem saldo ou apenas bônus), precisa depositar hoje
        $hasDepositToday = Deposit::where('user_id', $user->id)
            ->where('status', 1)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($hasDepositToday) {
            \Log::info('GameOpen: Acesso liberado - depositou hoje', [
                'user_id' => $user->id,
                'has_deposit_today' => true,
                'has_only_bonus' => $hasOnlyBonus,
                'timestamp' => now()
            ]);
            
            $message = $hasOnlyBonus 
                ? 'Acesso liberado. Você depositou hoje e pode jogar com saldo bônus.'
                : 'Acesso liberado. Você depositou hoje.';
            
            return response()->json([
                'can_open' => true,
                'message' => $message,
                'requires_deposit' => false,
                'balance_info' => [
                    'has_real_balance' => $hasRealBalance,
                    'has_any_balance' => $hasAnyBalance,
                    'has_only_bonus' => $hasOnlyBonus
                ]
            ]);
        } else {
            \Log::info('GameOpen: Acesso negado - sem saldo real e sem depósito hoje', [
                'user_id' => $user->id,
                'has_deposit_today' => false,
                'has_only_bonus' => $hasOnlyBonus,
                'timestamp' => now()
            ]);
            
            $message = $hasOnlyBonus 
                ? 'Você tem apenas saldo bônus. Para jogar, precisa fazer um depósito hoje.'
                : 'Você não tem saldo. Para jogar, precisa fazer um depósito hoje.';
            
            return response()->json([
                'can_open' => false,
                'message' => $message,
                'requires_deposit' => true,
                'action' => 'deposit',
                'balance_info' => [
                    'has_real_balance' => $hasRealBalance,
                    'has_any_balance' => $hasAnyBalance,
                    'has_only_bonus' => $hasOnlyBonus
                ]
            ]);
        }
    }
}
