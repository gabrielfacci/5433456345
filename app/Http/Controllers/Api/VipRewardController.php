<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VipReward;
use App\Models\UserVipReward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\PlayFiverService;
use App\Models\Transaction;

class VipRewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        // Buscar recompensas ativas
        $rewards = VipReward::where('status', 'active')
            ->orderBy('vip_level_required')
            ->orderBy('points_cost')
            ->get();

        // Mapear as recompensas com informações do usuário
        $rewards->transform(function ($reward) use ($user) {
            // Adicionar URL da imagem se existir
            if ($reward->image) {
                $reward->image_url = Storage::disk('public')->url($reward->image);
            } else {
                $reward->image_url = null;
            }

            // Adicionar ícone (usar o ícone personalizado ou padrão)
            $reward->display_icon = $reward->icon ?: $reward->getDefaultIcon();

            if ($user) {
              	
                $reward->can_claim = $reward->canBeClaimedBy($user);
                $reward->user_level = $user->wallet->vip_level ?? 0;
                $reward->user_points = $user->wallet ? $user->wallet->vip_points : 0;
                
                // Verificar se já foi resgatada (mesmo padrão das conquistas)
                $userReward = UserVipReward::where('user_id', $user->id)
                    ->where('vip_reward_id', $reward->id)
                    ->first();
                $reward->claimed = $userReward && $userReward->claimed_at;
                
                $reward->times_claimed = UserVipReward::where('user_id', $user->id)
                    ->where('vip_reward_id', $reward->id)
                    ->whereNotNull('claimed_at')
                    ->count();
                    
                // Verificar quantas vezes resgatou hoje
                $reward->times_claimed_today = UserVipReward::where('user_id', $user->id)
                    ->where('vip_reward_id', $reward->id)
                    ->whereDate('claimed_at', today())
                    ->count();
                    
                // Obter status detalhado
                $detailedStatus = $reward->getDetailedStatusForUser($user);
                $reward->status_details = $detailedStatus;
                $reward->can_claim_reason = !empty($detailedStatus['reasons']) ? implode(', ', $detailedStatus['reasons']) : null;
            } else {
                $reward->can_claim = false;
                $reward->user_level = 0;
                $reward->user_points = 0;
                $reward->times_claimed = 0;
                $reward->times_claimed_today = 0;
                $reward->claimed = false;
                $reward->status_details = null;
                $reward->can_claim_reason = null;
            }

            return $reward->only([
                'id', 'title', 'description', 'type', 'value', 'spins_quantity', 
                'vip_level_required', 'points_cost', 'display_icon', 'image_url',
                'daily_limit', 'total_limit', 'can_claim', 'user_level', 'user_points',
                'times_claimed', 'times_claimed_today', 'claimed', 'can_claim_reason'
            ]);
        });

        return response()->json([
            'rewards' => $rewards,
            'total' => $rewards->count(),
            'user_level' => $user ? $user->vip_level : 0,
            'user_points' => $user ? ($user->wallet ? $user->wallet->vip_points : 0) : 0
        ]);
    }

    /**
     * Resgatar uma recompensa.
     */
    public function claim(Request $request, $id)
    {
        $user = auth('api')->user();
        
        Log::info('Tentativa de resgate de recompensa VIP', [
            'user_id' => $user ? $user->id : 'null',
            'reward_id' => $id,
            'auth_check' => auth('api')->check()
        ]);
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $reward = VipReward::findOrFail($id);
        
        Log::info('Verificando recompensa VIP', [
            'reward_id' => $reward->id,
            'reward_type' => $reward->type,
            'reward_value' => $reward->value,
            'vip_level_required' => $reward->vip_level_required,
            'points_cost' => $reward->points_cost,
            'user_vip_level' => $user->wallet->vip_level ?? 0,
            'user_vip_points' => $user->wallet->vip_points ?? 0
        ]);

        // Verificar se pode resgatar
        if (!$reward->canBeClaimedBy($user)) {
            Log::warning('Usuário não pode resgatar recompensa VIP', [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'reward_title' => $reward->title,
                'user_vip_level' => $user->wallet->vip_level ?? 0,
                'required_level' => $reward->vip_level_required,
                'user_points' => $user->wallet->vip_points ?? 0,
                'required_points' => $reward->points_cost,
                'daily_limit' => $reward->daily_limit,
                'total_limit' => $reward->total_limit,
                'times_claimed_today' => $reward->userRewards()->where('user_id', $user->id)->whereDate('created_at', today())->count(),
                'times_claimed_total' => $reward->userRewards()->where('user_id', $user->id)->where('status', 'completed')->count(),
                'already_claimed' => $reward->userRewards()->where('user_id', $user->id)->where('status', 'completed')->exists()
            ]);
            
            // Verificar motivo específico para dar mensagem mais clara
            $errorMessage = 'Você não pode resgatar esta recompensa. ';
            
            if ($user->wallet->vip_level < $reward->vip_level_required) {
                $errorMessage .= "Nível VIP necessário: {$reward->vip_level_required}, seu nível: {$user->wallet->vip_level}";
            } elseif ($user->wallet->vip_points < $reward->points_cost) {
                $errorMessage .= "Pontos VIP necessários: {$reward->points_cost}, seus pontos: {$user->wallet->vip_points}";
            } else {
                // Verificar se já foi resgatada (mesmo padrão das conquistas)
                $userReward = UserVipReward::where('user_id', $user->id)
                    ->where('vip_reward_id', $reward->id)
                    ->first();
                    
                if ($userReward && $userReward->claimed_at) {
                    $errorMessage .= "Recompensa já foi resgatada";
                } elseif ($reward->daily_limit) {
                    $todayCount = $reward->userRewards()
                        ->where('user_id', $user->id)
                        ->whereDate('claimed_at', today())
                        ->count();
                    if ($todayCount >= $reward->daily_limit) {
                        $errorMessage .= "Limite diário atingido: {$todayCount}/{$reward->daily_limit}";
                    }
                } elseif ($reward->total_limit) {
                    $totalCount = $reward->userRewards()
                        ->where('user_id', $user->id)
                        ->whereNotNull('claimed_at')
                        ->count();
                    if ($totalCount >= $reward->total_limit) {
                        $errorMessage .= "Limite total atingido: {$totalCount}/{$reward->total_limit}";
                    }
                } else {
                    $errorMessage .= "Verifique seu nível VIP, pontos disponíveis e limites.";
                }
            }
            
            return response()->json(['error' => $errorMessage], 400);
        }

        DB::beginTransaction();
        
        try {
            Log::info('Iniciando resgate de recompensa VIP', [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'reward_type' => $reward->type,
                'reward_value' => $reward->value
            ]);
            
            $userReward = UserVipReward::create([
                'user_id' => $user->id,
                'vip_reward_id' => $reward->id,
                'amount_received' => $reward->type === 'spins' ? 0 : $reward->value,
                'spins_received' => $reward->type === 'spins' ? $reward->spins_quantity : null,
                'status' => 'completed',
                'claimed_at' => now(),
            ]);

            // Descontar pontos VIP do usuário
            if ($reward->points_cost > 0) {
                $user->wallet->decrement('vip_points', $reward->points_cost);
            }

            // Processar a recompensa baseado no tipo
            switch ($reward->type) {
                case 'money':
                    Log::info('Processando recompensa tipo money', ['value' => $reward->value]);
                    // Adicionar ao saldo do usuário
                    $user->wallet->increment('balance', $reward->value);
                    
                    // Criar transação
                     Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'vip_reward',
                        'amount' => $reward->value,
                        'description' => 'Recompensa VIP: ' . $reward->title,
                        'status' => 1
                    ]);
                    break;
                    
                case 'bonus':
                    Log::info('Processando recompensa tipo bonus', ['value' => $reward->value]);
                    // Adicionar ao saldo de bônus do usuário
                    $user->wallet->increment('balance_bonus', $reward->value);
                    
                    // Criar transação
                     Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'vip_bonus_reward',
                        'amount' => $reward->value,
                        'description' => 'Bônus VIP: ' . $reward->title,
                        'status' => 1
                    ]);
                    break;
                    
                case 'cashback':
                    // Adicionar ao saldo de cashback (se existir esse campo)
                    $user->wallet->increment('balance', $reward->value);
                    
                     Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'cashback_vip',
                        'amount' => $reward->value,
                        'description' => 'Cashback VIP: ' . $reward->title,
                        'status' => 1
                    ]);
                    break;
                    
                case 'spins':
                     $dados = [
                      "username" => $user->email,
                      "game_code" => $reward->game_id,
                      "rounds" => $reward->spins_quantity
                    ];
                    $roundsFree = PlayFiverService::RoundsFree($dados);

                    if(!$roundsFree['status']){
                        DB::rollBack();
                        return response()->json(['error' => 'Você não pode resgatar esta recompensa'], 400);
                    }
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'roundsSpins_vip',
                        'amount' => $reward->value,
                        'description' => 'Rodadas grátis: ' . $reward->title,
                        'status' => 1
                    ]);
                    break;
            }
			
            DB::commit();

            Log::info('Recompensa VIP resgatada com sucesso', [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'reward_type' => $reward->type,
                'reward_value' => $reward->value
            ]);
            
            // Verificar se ainda pode resgatar (deve ser false agora)
            $canStillClaim = $reward->canBeClaimedBy($user);
            Log::info('Pode ainda resgatar após o resgate?', ['can_claim' => $canStillClaim]);
            
            return response()->json([
                'success' => true,
                'message' => 'Recompensa resgatada com sucesso!',
                'reward' => $reward,
                'user_reward' => $userReward,
                'new_balance' => $user->wallet->fresh()->balance,
                'new_points' => $user->wallet->fresh()->vip_points,
                'can_claim' => $canStillClaim
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erro ao resgatar recompensa VIP', [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erro ao resgatar recompensa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histórico de recompensas do usuário.
     */
    public function history(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $history = UserVipReward::with('vipReward')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth('api')->user();
        $reward = VipReward::where('status', 'active')->findOrFail($id);
        
        // Adicionar URL da imagem
        if ($reward->image) {
            $reward->image_url = Storage::disk('public')->url($reward->image);
        }
        
        // Adicionar ícone
        $reward->display_icon = $reward->icon ?: $reward->getDefaultIcon();
        
        // Informações do usuário
        if ($user) {
            $reward->can_claim = $reward->canBeClaimedBy($user);
            $reward->user_level = $user->vip_level;
            $reward->user_points = $user->wallet ? $user->wallet->vip_points : 0;
        }
        
        return response()->json(['reward' => $reward]);
    }
} 