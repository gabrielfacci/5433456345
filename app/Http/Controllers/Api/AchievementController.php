<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Deposit;
use App\Models\MissionUser;
use App\Models\Order;

use App\Models\SpinRuns;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\Core as Helper;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user();

        $achievements = Achievement::where('status', 'active')->get();

        // IDs das conquistas desbloqueadas pelo usuário
        $userAchievements = $user
            ? UserAchievement::where('user_id', $user->id)->pluck('achievement_id')->toArray()
            : [];
        $userAchievementsResgatadas = $user
            ? UserAchievement::where('user_id', $user->id)->whereNotNull('unlocked_at')->pluck('achievement_id')->toArray()
            : [];

        $achievements = $achievements->map(function($achievement) use ($user, $userAchievements, $userAchievementsResgatadas) {
            $image_url = $achievement->image ? Storage::disk('public')->url($achievement->image) : null;

            // Calcular progresso real
            $progress = 0;
            $current = 0;
            if ($user) {
                switch ($achievement->requirement_type) {
                    case 'depositos':
                        $current = Deposit::where('user_id', $user->id)->sum('amount');
                        break;
                    case 'apostas':
                        $current = Order::where('user_id', $user->id)->count();
                        break;
                    case 'missoes':
                        $current = MissionUser::where('user_id', $user->id)->where('redeemed', 1)->count();
                        break;
                    case 'vitorias':
                        $current = Order::where('user_id', $user->id)->where('type', 'win')->count();
                        break;
                    default:
                        $current = 0;
                }
                $progress = min(100, round(($current / max(1, $achievement->requirement_value)) * 100));
            }

            $unlocked = in_array($achievement->id, $userAchievements) || $progress >= 100;
            $claimed = in_array($achievement->id, $userAchievementsResgatadas);

            return [
                'id' => $achievement->id,
                'title' => $achievement->title,
                'description' => $achievement->description,
                'icon' => $achievement->icon,
                'image_url' => $image_url,
                'vip_points_reward' => $achievement->vip_points_reward,
                'requirement_type' => $achievement->requirement_type,
                'requirement_value' => $achievement->requirement_value,
                'status' => $achievement->status,
                'total_limit' => $achievement->total_limit,
                'progress' => $progress,
                'current' => $current,
                'unlocked' => $unlocked,
                'claimed' => $claimed,
            ];
        });

        return response()->json(['achievements' => $achievements]);
    }

    public function claim(Request $request, $id)
    {
        $user = $request->user();
        $achievement = Achievement::findOrFail($id);

        // Verifica se já foi resgatada
        $userAchievement = UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->first();
        if ($userAchievement && $userAchievement->unlocked_at) {
            return response()->json(['error' => 'Conquista já resgatada'], 400);
        }

        // Verifica se pode resgatar
        $progress = 0;
        $order = Order::where("user_id", $user->id);

        switch ($achievement->requirement_type) {
            case 'depositos':
                $progress = Deposit::where('user_id', $user->id)->sum('amount');
                break;
            case 'apostas':
                $progress = $order->where('type', 'bet')->count();
                break;
            case 'missoes':
                $progress = MissionUser::where('user_id', $user->id)->where('redeemed', 1)->count();
                break;
            case 'vitorias':
                $progress = $order->where('type', 'win')->count();
                break;
            default:
                $progress = 0;
        }
        if ($progress < $achievement->requirement_value) {
            return response()->json(['error' => 'Você ainda não completou o requisito desta conquista'], 400);
        }

        DB::beginTransaction();
        try {
            // Marca como resgatada
            if (!$userAchievement) {
                $userAchievement = UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                    'unlocked_at' => now(),
                ]);
            } else {
                $userAchievement->update(['unlocked_at' => now()]);
            }

            // Credita os VIP Points
            if ($achievement->vip_points_reward > 0 && $user->wallet) {
                $oldVipLevel = $user->wallet->vip_level;
                $oldVipPoints = $user->wallet->vip_points;
                
                $user->wallet->increment('vip_points', $achievement->vip_points_reward);
                
                // INTEGRAÇÃO COM SISTEMA VIP - Atualiza nível VIP automaticamente
                $this->updateVipLevel($user);
                
                // Recarrega os dados do usuário para pegar os valores atualizados
                $user->refresh();
                $user->wallet->refresh();
                
                $newVipLevel = $user->wallet->vip_level;
                $newVipPoints = $user->wallet->vip_points;
                
                Log::info("Conquista resgatada - VIP Level: {$oldVipLevel} -> {$newVipLevel}, VIP Points: {$oldVipPoints} -> {$newVipPoints}");
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Recompensa de conquista resgatada com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao resgatar conquista: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza o nível VIP do usuário baseado nos pontos VIP atuais
     */
    private function updateVipLevel($user)
    {
        $wallet = $user->wallet;
        if (!$wallet) return;

        // Usa a função helper do Core.php para atualizar o nível VIP
        Helper::updateVipLevel($wallet);
    }
} 