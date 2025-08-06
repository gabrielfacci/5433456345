<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Wallet;
use App\Models\AffiliateHistory;
use Illuminate\Support\Facades\Auth;

class TestAffiliateApiResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:affiliate-api-response {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a resposta da API de afiliado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário não encontrado!");
            return;
        }
        
        // Simular autenticação
        Auth::login($user);
        
        $this->info("Testando resposta da API para usuário: {$user->name} (ID: {$user->id})");
        $this->newLine();
        
        // Simular a lógica da API
        $indications = User::where('inviter', $user->id)->count();
        $walletDefault = Wallet::where('user_id', $user->id)->first();
        
        // IDs dos usuários indicados pelo afiliado
        $invitedUsersIds = User::where('inviter', $user->id)->pluck('id');
        
        // Total de depósitos realizados pelos indicados
        $totalDeposits = \App\Models\Deposit::whereIn('user_id', $invitedUsersIds)->count();
        
        // Total de depositantes únicos
        $totalDepositors = \App\Models\Deposit::whereIn('user_id', $invitedUsersIds)->distinct('user_id')->count('user_id');
        
        // Soma do valor depositado
        $totalDepositedAmount = \App\Models\Deposit::whereIn('user_id', $invitedUsersIds)->sum('amount');
        
        $response = [
            'status' => true,
            'code' => $user->inviter_code,
            'url' => config('app.url') . '/register?code=' . $user->inviter_code,
            'indications' => $indications,
            'total_deposits' => $totalDeposits,
            'total_depositors' => $totalDepositors,
            'total_deposited_amount' => $totalDepositedAmount,
            'wallet' => $walletDefault
        ];
        
        $this->info("=== RESPOSTA DA API ===");
        $this->info(json_encode($response, JSON_PRETTY_PRINT));
        $this->newLine();
        
        $this->info("=== VALORES ESPECÍFICOS ===");
        $this->info("refer_rewards: " . ($walletDefault->refer_rewards ?? 0));
        $this->info("currency: " . ($walletDefault->currency ?? 'BRL'));
        $this->info("Valor formatado: R$ " . number_format($walletDefault->refer_rewards ?? 0, 2, ',', '.'));
        
        // Testar a função currencyFormat
        $this->info("=== TESTE DE FORMATAÇÃO ===");
        $value = $walletDefault->refer_rewards ?? 0;
        $currency = $walletDefault->currency ?? 'BRL';
        
        $formatted = $this->currencyFormat($value, $currency);
        $this->info("currencyFormat($value, $currency): $formatted");
    }
    
    private function currencyFormat($value, $currency)
    {
        if ($value === null || $currency === null) {
            $currency = 'USD';
        }

        return number_format($value, 2, ',', '.');
    }
} 