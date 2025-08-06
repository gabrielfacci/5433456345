<?php

namespace App\Http\Controllers\Api\Missions;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Order;

use App\Models\MissionUser;
use App\Models\Transaction;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
                $user = auth('api')->user();

        
        // Buscar missões ativas
        $missions = Mission::where('status', 'active')
            ->get();

        // Mapear as missões com progresso do usuário
        $missions->transform(function ($mission) use ($user) {
            // Buscar progresso do usuário para esta missão
            $userProgress = null;
            $progress_percentage = 0;
            
            if ($user) {
                $userProgress = MissionUser::where('user_id', $user->id)
                    ->where('mission_id', $mission->id)
                    ->first();
                Log::info("opa");
                // Calcular progresso baseado no tipo de missão
                $current_progress = $this->calculateUserProgress($user, $mission);
                $progress_percentage = min(100, ($current_progress / $mission->target_amount) * 100);
            }

            // Adicionar URL da imagem se existir
            if ($mission->image) {
                $mission->image_url = Storage::disk('public')->url($mission->image);
            } else {
                $mission->image_url = null;
            }

            // Adicionar informações de progresso
            $mission->user_progress = $current_progress ?? 10;
            $mission->progress_percentage = round($progress_percentage, 2);
            $mission->is_completed = $progress_percentage >= 100;
            $mission->is_claimed = $userProgress ? $userProgress->redeemed : false;

            return $mission->only([
                'id', 'title', 'description', 'type', 'target_amount', 
                'reward', 'reward_type', 'reward_spins', 'image_url',
                'user_progress', 'progress_percentage', 'is_completed', 'is_claimed'
            ]);
        });

        return response()->json([
            'missions' => $missions,
            'total' => $missions->count()
        ]);
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
        $user = Auth::user();
        $mission = Mission::where('status', 'active')->findOrFail($id);
        
        // Calcular progresso
        $current_progress = 0;
        $userProgress = null;
        
        if ($user) {
            $userProgress = MissionUser::where('user_id', $user->id)
                ->where('mission_id', $mission->id)
                ->first();
                
            $current_progress = $this->calculateUserProgress($user, $mission);
        }
        
        $progress_percentage = min(100, ($current_progress / $mission->target_amount) * 100);
        
        // Adicionar URL da imagem
        if ($mission->image) {
            $mission->image_url = Storage::disk('public')->url($mission->image);
        }
        
        $mission->user_progress = $current_progress;
        $mission->progress_percentage = round($progress_percentage, 2);
        $mission->is_completed = $progress_percentage >= 100;
        $mission->is_claimed = $userProgress ? $userProgress->redeemed : false;
        
        return response()->json(['mission' => $mission]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
