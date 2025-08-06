<?php

namespace App\Http\Controllers\Api\Missions;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\PlayFiverService;

class MissionUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $userMissions = MissionUser::where('user_id', $user->id)
            ->with('mission')
            ->get();
            
        return response()->json(['user_missions' => $userMissions]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage (Resgatar recompensa).
     */
    public function store(Request $request)
    {
        $rules = [
            'mission_id' => 'required|exists:missions,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Auth::user();
        $mission = Mission::findOrFail($request->mission_id);
        
        // Verificar se a missão está ativa
        if ($mission->status !== 'active') {
            return response()->json(['error' => 'Missão não está ativa'], 400);
        }
        
        // Calcular progresso atual do usuário
        $currentProgress = $this->calculateUserProgress($user, $mission);
        $isCompleted = $currentProgress >= $mission->target_amount;
        
        if (!$isCompleted) {
            return response()->json(['error' => 'Missão ainda não foi completada'], 400);
        }
        
        // Verificar se já foi resgatada
        $existingClaim = MissionUser::where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->first();
            
        if ($existingClaim && $existingClaim->redeemed) {
            return response()->json(['error' => 'Recompensa já foi resgatada'], 400);
        }

        DB::beginTransaction();
        
        try {
            // Criar ou atualizar registro de missão do usuário
            $missionUser = MissionUser::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'mission_id' => $mission->id
                ],
                [
                    'reward' => $mission->reward_type === 'money' ? $mission->reward : $mission->reward_spins,
                    
                ]
            );
            
            // Aplicar recompensa
            if ($mission->reward_type === 'money') {
                // Adicionar dinheiro à carteira
                $user->wallet->increment('balance', $mission->reward);
                
                // Registrar transação
                Transaction::create([
                    'type' => 'mission_reward',
                    'amount' => $mission->reward,
                    'description' => "Recompensa da missão: {$mission->title}",
                    'reference' => "mission_{$mission->id}"
                ]);
                
                $rewardText = "R$ " . number_format($mission->reward, 2, ',', '.');
                
            } elseif ($mission->reward_type === 'rounds_free') {
                 $dados = [
                  "username" => $user->email,
                  "game_code" => $mission->reward_game_id,
                  "rounds" => $mission->reward_spins
                ];
                $roundsFree = PlayFiverService::RoundsFree($dados);
              	
                if(!$roundsFree['status']){
                  	DB::rollBack();
                   	return response()->json(['error' => 'Ocorreu um erro ao resgatar o prêmio.'], 400);
                }
                
                $rewardText = $mission->reward_spins . " rodadas grátis";
                
                // Aqui você pode implementar a lógica específica para rodadas grátis
                // Por exemplo, criar um registro na tabela de bônus ou rodadas grátis
            }
            $missionUser->update(["redeemed" => true]);
            DB::commit();
            
            return response()->json([
                'status' => true, 
                'message' => "Recompensa resgatada com sucesso! Você recebeu: {$rewardText}",
                'reward' => $rewardText
            ]);
            
        } catch (\Exception $e) {
          Log::info($e);
            DB::rollBack();
            return response()->json(['error' => 'Erro ao resgatar recompensa'], 500);
        }
    }

    /**
     * Calcular progresso do usuário baseado no tipo de missão
     */
    private function calculateUserProgress($user, $mission)
    {
      $order = Order::where("user_id", $user->id);
        switch ($mission->type) {
            case 'deposit':
                // Somar todos os depósitos do usuário
                return Transaction::where("user_id", $user->id)->where('status', 1)->sum('price');
                
            case 'total_bet':
                // Somar todas as apostas do usuário
                return $order
                    ->where('type', 'bet')
                    ->sum('amount');
                    
            case 'game_bet':
                // Somar apostas em um jogo específico
                if ($mission->game_id) {
                    return $order
                        ->where('type', 'bet')
                        ->where('game', $mission->game_id)
                        ->sum('amount');
                }
                return 0;
                
            case 'rounds_played':
                // Contar rodadas jogadas
                if ($mission->game_id) {
                    return $order
                        ->where('type', 'bet')
                        ->where('game', $mission->game_id)
                        ->count();
                }
                return $order->transactions()
                    ->where('type', 'bet')
                    ->count();
                    
            case 'win_amount':
                // Somar ganhos
                if ($mission->game_id) {
                    return $order
                        ->where('type', 'win')
                        ->where('game', $mission->game_id)
                        ->sum('amount');
                }
                return $order->transactions()
                    ->where('type', 'win')
                    ->sum('amount');
                    
            case 'loss_amount':
                // Somar perdas (apostas - ganhos)
                $bets = $order->where('type', 'bet');
                $wins = $order->where('type', 'win');
                
                if ($mission->game_id) {
                    $bets = $bets->where('game', $mission->game_id);
                    $wins = $wins->where('game', $mission->game_id);
                }
                
                return $bets->sum('amount') - $wins->sum('amount');
                
            default:
                return 0;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
