<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Retorna os dados do perfil do usuário
     */
    public function index()
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Busca estatísticas do usuário
        $totalEarnings = $user->earnings ?? 0;
        $totalLosses = $user->losses ?? 0;
        $totalBets = $user->total_bets ?? 0;
        $sumBets = $user->sum_bets ?? 0;
        $winsCount = $user->wins_count ?? 0;
        
        // Calcula taxa de vitória
        $winRate = $totalBets > 0 ? round(($winsCount / $totalBets) * 100, 2) : 0;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'cpf' => $user->cpf,
                'avatar' => $user->avatar,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip_code' => $user->zip_code,
                'dateHumanReadable' => $user->created_at ? Carbon::parse($user->created_at)->format('d/m/Y') : 'N/A',
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'totalEarnings' => $totalEarnings,
            'totalLosses' => $totalLosses,
            'totalBets' => $totalBets,
            'sumBets' => $sumBets,
            'winRate' => $winRate,
            'winsCount' => $winsCount,
        ]);
    }

    /**
     * Upload de avatar
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $file = $request->file('avatar');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar atualizado com sucesso',
                'avatar' => $path
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao fazer upload do avatar'], 500);
        }
    }

    /**
     * Atualizar nome do usuário
     */
    public function updateName(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            $user->update(['name' => $request->name]);

            return response()->json([
                'success' => true,
                'message' => 'Nome atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar nome'], 500);
        }
    }

    /**
     * Atualizar telefone do usuário
     */
    public function updatePhone(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $request->validate([
            'phone' => 'required|string|max:20'
        ]);

        try {
            $user->update(['phone' => $request->phone]);

            return response()->json([
                'success' => true,
                'message' => 'Telefone atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar telefone'], 500);
        }
    }

    /**
     * Atualizar informações de entrega
     */
    public function updateDelivery(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10'
        ]);

        try {
            $updateData = [];
            
            if ($request->has('address')) {
                $updateData['address'] = $request->address;
            }
            if ($request->has('city')) {
                $updateData['city'] = $request->city;
            }
            if ($request->has('state')) {
                $updateData['state'] = $request->state;
            }
            if ($request->has('zip_code')) {
                $updateData['zip_code'] = $request->zip_code;
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Informações de entrega atualizadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar informações de entrega'], 500);
        }
    }

    /**
     * Buscar apostas recentes
     */
    public function getRecentBets()
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        try {
            // Aqui você pode implementar a lógica para buscar apostas recentes
            // Por enquanto, retornamos um array vazio
            $bets = [];

            return response()->json([
                'bets' => $bets
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar apostas recentes'], 500);
        }
    }

    /**
     * Buscar histórico de apostas
     */
    public function getBetHistory()
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        try {
            // Aqui você pode implementar a lógica para buscar histórico de apostas
            // Por enquanto, retornamos um array vazio
            $bets = [];

            return response()->json([
                'bets' => $bets
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar histórico de apostas'], 500);
        }
    }

    /**
     * Raspadinha Básica - R$1,00
     */
    public function playScratchCard()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 1.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'R$3,00',     'value' => 3.00,    'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/3_reais.png')],   
            ['name' => 'R$15,00',    'value' => 15.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/15_reais_5fbfe586.png')],
            ['name' => 'R$10,00',    'value' => 10.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/10_reais.png')],
            ['name' => 'R$5,00',     'value' => 5.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5_reais.png')],
            ['name' => 'R$2,00',     'value' => 2.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/2_reais.png')],
            ['name' => 'R$7,00',   'value' => 7.00,  'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/7-reais.png')],
            ['name' => 'R$100,00',   'value' => 100.00,  'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/100_reais.jpg')],
            ['name' => 'R$1,00',     'value' => 1.00,    'win_chance' => $isInfluencer ? 100 : 5,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/1_reais.png')],
            ['name' => 'R$6,00',    'value' => 6.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-1.png')],
            ['name' => 'R$25,00',    'value' => 25.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/25_reais_1d140f81.png')],
            ['name' => 'R$4,00',  'value' => 4.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-2.png')],
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 5);
    }

    /**
     * Raspadinha Cinco Mil - R$2,00
     */
    public function playScratchCardCincoMil()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 2.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'R$3,00',     'value' => 3.00,    'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/3_reais.png')],   
            ['name' => 'R$15,00',    'value' => 15.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/15_reais_5fbfe586.png')],
            ['name' => 'R$10,00',    'value' => 10.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/10_reais.png')],
            ['name' => 'R$5,00',     'value' => 5.00,    'win_chance' => $isInfluencer ? 100 : 3,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5_reais.png')],
            ['name' => 'R$2,00',     'value' => 2.00,    'win_chance' => $isInfluencer ? 100 : 6,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/2_reais.png')],
            ['name' => 'R$7,00',   'value' => 7.00,  'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/7-reais.png')],
            ['name' => 'R$120,00',   'value' => 120.00,  'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/100_reais.jpg')],
            ['name' => 'R$1,00',     'value' => 1.00,    'win_chance' => $isInfluencer ? 100 : 10,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/1_reais.png')],
            ['name' => 'R$6,00',    'value' => 6.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-1.png')],
            ['name' => 'R$25,00',    'value' => 25.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/25_reais_1d140f81.png')],
            ['name' => 'R$4,00',  'value' => 4.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-2.png')],
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 5);
    }

    /**
     * Raspadinha Dez Mil - R$5,00
     */
    public function playScratchCardDezMil()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 5.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'R$3,00',     'value' => 3.00,    'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/3_reais.png')],   
            ['name' => 'R$15,00',    'value' => 15.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/15_reais_5fbfe586.png')],
            ['name' => 'R$10,00',    'value' => 10.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/10_reais.png')],
            ['name' => 'R$5,00',     'value' => 5.00,    'win_chance' => $isInfluencer ? 100 : 3,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5_reais.png')],
            ['name' => 'R$2,00',     'value' => 2.00,    'win_chance' => $isInfluencer ? 100 : 10,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/2_reais.png')],
            ['name' => 'R$7,00',   'value' => 7.00,  'win_chance' => $isInfluencer ? 100 : 1,  'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/7-reais.png')],
            ['name' => 'R$300,00',   'value' => 300.00,  'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/100_reais.jpg')],
            ['name' => 'R$1,00',     'value' => 1.00,    'win_chance' => $isInfluencer ? 100 : 20,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/1_reais.png')],
            ['name' => 'R$6,00',    'value' => 6.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-1.png')],
            ['name' => 'R$25,00',    'value' => 25.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/25_reais_1d140f81.png')],
            ['name' => 'R$4,00',  'value' => 4.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-2.png')],
            ['name' => 'R$50,00',    'value' => 50.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/50_reais.png')],
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 5);
    }

    /**
     * Raspadinha Milhão - R$25,00
     */
    public function playScratchCardMilhao()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 25.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'AirPods Max',     'value' => 4200.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5d89384c-6a70-46bd-a5a1-647773875758.png')],
            ['name' => 'R$700,00',     'value' => 700.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/150_reais_b179f726.png')],
            ['name' => 'R$3,00',     'value' => 3.00,    'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/3_reais.png')],   
            ['name' => 'R$15,00',    'value' => 15.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/15_reais_5fbfe586.png')],
            ['name' => 'R$10,00',    'value' => 10.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/10_reais.png')],
            ['name' => 'R$5,00',     'value' => 5.00,    'win_chance' => $isInfluencer ? 100 : 10,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5_reais.png')],
            ['name' => 'R$2,00',     'value' => 2.00,    'win_chance' => $isInfluencer ? 100 : 6,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/2_reais.png')],
            ['name' => 'R$7,00',   'value' => 7.00,  'win_chance' => $isInfluencer ? 100 : 1,  'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/7-reais.png')],
            ['name' => 'R$100,00',   'value' => 100.00,  'win_chance' => $isInfluencer ? 100 : 1,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/100_reais.jpg')],
            ['name' => 'R$1,00',     'value' => 1.00,    'win_chance' => $isInfluencer ? 100 : 50,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/1_reais.png')],
            ['name' => 'R$6,00',    'value' => 6.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-1.png')],
            ['name' => 'R$25,00',    'value' => 25.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/25_reais_1d140f81.png')],
            ['name' => 'R$4,00',  'value' => 4.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/4-reais-2.png')],
            ['name' => 'R$50,00',    'value' => 50.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/50_reais.png')],
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 30);
    }

    /**
     * Raspadinha Make - R$50,00
     */
    public function playScratchCardMake()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 50.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'AirPods Max',     'value' => 0.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5d89384c-6a70-46bd-a5a1-647773875758.png')],
            ['name' => 'R$150,00',     'value' => 150.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/150_reais_b179f726.png')],
            ['name' => 'R$15,00',    'value' => 15.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/15_reais_5fbfe586.png')],
            ['name' => 'R$10,00',    'value' => 10.00,   'win_chance' => $isInfluencer ? 100 : 10,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/10_reais.png')],
            ['name' => 'R$100,00',   'value' => 100.00,  'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/100_reais.jpg')],
            ['name' => 'JBL',        'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/JBL_944de913.webp')],
            ['name' => 'Iphone',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/iphone12_white_12897651.webp')],
            ['name' => 'R$50,00',    'value' => 50.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/50_reais.png')],
            ['name' => 'IPAD',        'value' => 3900.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/721d5399-2171-495e-877a-5c051a5caf99.png')],
            ['name' => 'R$500,00',   'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
            ['name' => 'R$250,00',   'value' => 250.00,  'win_chance' => $isInfluencer ? 10 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 30);
    }

    /**
     * Raspadinha 6 - R$60,00
     */
    public function playScratchCard6()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 60.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'AirPods Max',     'value' => 0.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5d89384c-6a70-46bd-a5a1-647773875758.png')],
            ['name' => 'R$150,00',     'value' => 150.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/150_reais_b179f726.png')],
            ['name' => 'R$15,00',    'value' => 15.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/15_reais_5fbfe586.png')],
            ['name' => 'R$10,00',    'value' => 10.00,   'win_chance' => $isInfluencer ? 100 : 10,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/10_reais.png')],
            ['name' => 'R$100,00',   'value' => 100.00,  'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/100_reais.jpg')],
            ['name' => 'JBL',        'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/JBL_944de913.webp')],
            ['name' => 'Iphone',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/iphone12_white_12897651.webp')],
            ['name' => 'R$50,00',    'value' => 50.00,   'win_chance' => $isInfluencer ? 100 : 0,   'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/50_reais.png')],
            ['name' => 'IPAD',        'value' => 3900.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/721d5399-2171-495e-877a-5c051a5caf99.png')],
            ['name' => 'R$500,00',   'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
            ['name' => 'R$250,00',   'value' => 250.00,  'win_chance' => $isInfluencer ? 10 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
            ['name' => 'R$550,00',   'value' => 550.00,  'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
            ['name' => 'R$350,00',   'value' => 350.00,  'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
            ['name' => 'R$700,00',   'value' => 700.00,  'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
            ['name' => 'R$650,00',   'value' => 650.00,  'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/saco_dinheiro_4b71930f-1.webp')],
           
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 1);
    }

    /**
     * Raspadinha 7 - R$80,00
     */
    public function playScratchCard7()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 80.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'AirPods Max',     'value' => 0.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5d89384c-6a70-46bd-a5a1-647773875758.png')],
            ['name' => 'JBL',        'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/JBL_944de913.webp')],
            ['name' => 'Iphone 12',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/iphone12_white_12897651.webp')],
            ['name' => 'IPAD',        'value' => 3900.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/721d5399-2171-495e-877a-5c051a5caf99.png')],
            ['name' => 'Maquina de lavar',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-2.png')],
            ['name' => 'Ventilador',     'value' => 100.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-4.png')],
            ['name' => 'Air fryer',     'value' => 800.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-5.png')],
            ['name' => 'Liquidificador',     'value' => 249.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-6.png')],
            ['name' => 'Iphone 15',     'value' => 4000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/iphone-15.png')],
            ['name' => 'Guarda Roupa',     'value' => 3000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-3.png')],
           
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 0);
    }

    /**
     * Raspadinha 8 - R$100,00
     */
    public function playScratchCard8()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 100.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'AirPods Max',     'value' => 0.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5d89384c-6a70-46bd-a5a1-647773875758.png')],
            ['name' => 'JBL',        'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/JBL_944de913.webp')],
            ['name' => 'Iphone 12',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/iphone12_white_12897651.webp')],
            ['name' => 'IPAD',        'value' => 3900.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/721d5399-2171-495e-877a-5c051a5caf99.png')],
            ['name' => 'Maquina de lavar',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-2.png')],
            ['name' => 'Ventilador',     'value' => 100.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-4.png')],
            ['name' => 'Air fryer',     'value' => 800.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-5.png')],
            ['name' => 'Liquidificador',     'value' => 249.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-6.png')],
            ['name' => 'Iphone 15',     'value' => 4000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/iphone-15.png')],
            ['name' => 'Guarda Roupa',     'value' => 3000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-3.png')],
            ['name' => 'Bolsa Prada',     'value' => 17000.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/8610294e-6244-48bf-a06b-9b69dc3336cf.png')],
            ['name' => 'Honda CB 2025',   'value' => 0.00,  'win_chance' => $isInfluencer ? 0 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/imagem-home-honda-cb-300f-twister-abs-lateral-azul.webp')],
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 0);
    }

    /**
     * Raspadinha 9 - R$120,00
     */
    public function playScratchCard9()
    {
        $user = auth('api')->user();
        $isInfluencer = $user->is_demo_agent == 1;
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $cost = 120.00;
        $balanceTotal = $wallet->balance + $wallet->balance_withdrawal;

        if ($balanceTotal < $cost) {
            return response()->json(['error' => 'Saldo insuficiente para jogar.'], 400);
        }

        // Desconta o valor
        if ($wallet->balance >= $cost) {
            $wallet->decrement('balance', $cost);
        } else {
            $remaining = $cost - $wallet->balance;
            $wallet->update(['balance' => 0]);
            $wallet->decrement('balance_withdrawal', $remaining);
        }

        // Pool de prêmios
        $itemsPool = [
            ['name' => 'Bolsa Prada',     'value' => 17000.00,    'win_chance' => $isInfluencer ? 100 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/8610294e-6244-48bf-a06b-9b69dc3336cf.png')],
            ['name' => 'AirPods Max',     'value' => 0.00, 'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/5d89384c-6a70-46bd-a5a1-647773875758.png')],
            ['name' => 'JBL',        'value' => 500.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/JBL_944de913.webp')],
            ['name' => 'Iphone 12',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/iphone12_white_12897651.webp')],
            ['name' => 'IPAD',        'value' => 3900.00,  'win_chance' => $isInfluencer ? 10 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/721d5399-2171-495e-877a-5c051a5caf99.png')],
            ['name' => 'Maquina de lavar',     'value' => 2000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-2.png')],
            ['name' => 'Ventilador',     'value' => 100.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-4.png')],
            ['name' => 'Air fryer',     'value' => 800.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-5.png')],
            ['name' => 'Liquidificador',     'value' => 249.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-6.png')],
            ['name' => 'Iphone 15',     'value' => 4000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/iphone-15.png')],
            ['name' => 'Guarda Roupa',     'value' => 3000.00, 'win_chance' => $isInfluencer ? 0 : 0, 'image' => url('https://queminvestecresce.com.br/wp-content/uploads/2025/07/image-removebg-preview-3.png')],
            ['name' => 'BMW X1',        'value' => 0.00,  'win_chance' => $isInfluencer ? 100 : 0, 'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/model_main_webp_comprar-sdrive20i-x-line_3c6b55fef4.png.png')],
            ['name' => 'Honda CB 2025',   'value' => 0.00,  'win_chance' => $isInfluencer ? 0 : 0,  'image' => url('https://worldgamesbr.com.br/wp-content/uploads/2025/07/imagem-home-honda-cb-300f-twister-abs-lateral-azul.webp')],    
        ];

        return $this->processScratchCard($itemsPool, $isInfluencer, $wallet, $user, 100, 0);
    }

    /**
     * Método auxiliar para processar a raspadinha
     * Usa is_demo_agent para identificar contas de influenciadores
     */
    private function processScratchCard($itemsPool, $isInfluencer, $wallet, $user, $influencerChance = 80, $normalChance = 1)
    {
        // Gera pool ponderada com base no win_chance
        $weightedPool = [];
        foreach ($itemsPool as $item) {
            $reps = (int)($item['win_chance'] * 10);
            for ($i = 0; $i < $reps; $i++) {
                $weightedPool[] = $item;
            }
        }

        $items = [];
        $win = false;
        $value = 0;
        $winningItemName = '';

        // Sorteio: o jogador vai ganhar?
        $winChanceGlobal = $isInfluencer ? $influencerChance : $normalChance;
        if (!empty($weightedPool) && mt_rand(1, 100) <= $winChanceGlobal) {
            // Sorteia o item que aparecerá 3x
            $winningItem = $weightedPool[array_rand($weightedPool)];
            $value = $winningItem['value'];
            $winningItemName = $winningItem['name'];

            // Posições aleatórias para o item vencedor
            $indexes = array_rand(range(0, 8), 3);
            if (!is_array($indexes)) $indexes = [$indexes];
            $items = array_fill(0, 9, null);
            foreach ($indexes as $i) {
                $items[$i] = $winningItem;
            }

            // Preenche os demais espaços sem repetir 3x outros itens
            foreach ($items as $i => $slot) {
                if ($slot === null) {
                    do {
                        $randItem = $itemsPool[array_rand($itemsPool)];
                        $count = count(array_filter($items, fn($it) => $it && $it['name'] === $randItem['name']));
                    } while ($count >= 2);
                    $items[$i] = $randItem;
                }
            }

            $wallet->increment('balance_withdrawal', $value);
            if (!$isInfluencer && $value > 0) {
                $user->increment('earnings', $value);
            }
            $win = true;
        } else {
            // Não ganhou: preenche com prêmios aleatórios sem repetir 3x nenhum
            while (count($items) < 9) {
                $item = $itemsPool[array_rand($itemsPool)];
                $count = count(array_filter($items, fn($i) => $i['name'] === $item['name']));
                if ($count < 2) {
                    $items[] = $item;
                }
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items,
            'win' => $win,
            'value' => $value,
            'winningItemName' => $winningItemName,
            'newBalance' => $wallet->balance_withdrawal,
        ]);
    }
}
