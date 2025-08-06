<?php

namespace App\Http\Controllers\Gift;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftRedeem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PlayFiverService;

class GiftController extends Controller
{
    /**
     * Resgatar prêmio pelo código
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Priorizar o usuário autenticado, depois um ID do request
        if ($request->has('user_id')) {
            $requestUserId = $request->user_id;
            \Log::info('ID do usuário enviado na requisição: ' . $requestUserId);
            $user = \App\Models\User::find($requestUserId);
            if ($user) {
                $userId = $requestUserId;
                \Log::info('Usando ID do usuário da requisição: ' . $userId);
            } else {
                \Log::warning('Usuário da requisição não encontrado: ' . $requestUserId);
                if (Auth::check()) {
                    $userId = Auth::id();
                    $user = Auth::user();
                    \Log::info('Fallback para usuário autenticado: ' . $userId);
                } else {
                    return response()->json(['error' => 'Usuário não encontrado'], 404);
                }
            }
        } else if (Auth::check()) {
            $userId = Auth::id();
            $user = Auth::user();
            \Log::info('Usando usuário autenticado: ' . $userId);
        } else {
            return response()->json(['error' => 'É necessário fornecer um ID de usuário'], 422);
        }

        $code = $request->input('code');

        $gift = Gift::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$gift) {
            return response()->json(['message' => 'Código inválido ou premiação inativa.'], 404);
        }

        if ($gift->quantity <= 0) {
            return response()->json(['message' => 'Este prêmio já foi totalmente resgatado.'], 400);
        }

        // Verifica se o usuário já resgatou esse gift
        $alreadyRedeemed = GiftRedeem::where('gift_id', $gift->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadyRedeemed) {
            return response()->json(['message' => 'Você já resgatou este prêmio.'], 400);
        }

     

        if($gift->spins && $gift->spins != null){
            $dados = [
                "username" => $user->email,
                "game_code" => $gift->game_code,
                "rounds" => $gift->spins
            ];
            $roundsFree = PlayFiverService::RoundsFree($dados);
          	if(!$roundsFree['status']){
               return response()->json(['message' => 'Ocorreu um erro ao resgatar o prêmio.'], 400);
            }
        }
        if ($gift->amount && $gift->amount > 0) {
            $wallet = \App\Models\Wallet::where('user_id', $user->id)->where('active', 1)->first();
            \Log::info('Wallet antes', ['user_id' => $user->id, 'balance_withdrawal' => $wallet?->balance_withdrawal]);
            if ($wallet) {
                $wallet->balance_withdrawal += $gift->amount;
                $wallet->save();
                \Log::info('Wallet depois', ['user_id' => $user->id, 'balance_withdrawal' => $wallet->balance_withdrawal]);
            } else {
                \Log::warning('Wallet não encontrada para usuário', ['user_id' => $user->id]);
            }
        }
   // Cria o resgate
        $redeem = GiftRedeem::create([
            'gift_id' => $gift->id,
            'user_id' => $user->id,
            'amount' => $gift->amount,
            'spins' => $gift->spins,
            'code' => $gift->code,
            'is_used' => true,
        ]);
        // Decrementa a quantidade disponível
        $gift->decrement('quantity');

        return response()->json([
            'message' => 'Prêmio resgatado com sucesso!',
            'redeem' => $redeem,
        ]);
    }

    /**
     * Listar resgates de prêmios do usuário
     */
    public function listRedeems(Request $request)
{
    // Priorizar o usuário autenticado, depois um ID do request
    if ($request->has('user_id')) {
        $requestUserId = $request->user_id;
        \Log::info('ID do usuário enviado na requisição: ' . $requestUserId);
        $user = \App\Models\User::find($requestUserId);
        if ($user) {
            $userId = $requestUserId;
            \Log::info('Usando ID do usuário da requisição: ' . $userId);
        } else {
            \Log::warning('Usuário da requisição não encontrado: ' . $requestUserId);
            if (Auth::check()) {
                $userId = Auth::id();
                $user = Auth::user();
                \Log::info('Fallback para usuário autenticado: ' . $userId);
            } else {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }
        }
    } else if (Auth::check()) {
        $userId = Auth::id();
        $user = Auth::user();
        \Log::info('Usando usuário autenticado: ' . $userId);
    } else {
        return response()->json(['error' => 'É necessário fornecer um ID de usuário'], 422);
    }

    // Busca os resgates com os dados do gift associado
    $redeems = GiftRedeem::with('gift')
        ->where('user_id', $user->id)
        ->latest()
        ->get()
        ->map(function ($redeem) {
            return [
                'id' => $redeem->id,
                'name' => optional($redeem->gift)->name ?? 'Prêmio',
                'amount' => $redeem->amount,
                'spins' => $redeem->spins,
                'created_at' => $redeem->created_at,
            ];
        });

    return response()->json($redeems);

    }
}
