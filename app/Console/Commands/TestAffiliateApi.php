<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Wallet;
use App\Models\AffiliateHistory;

class TestAffiliateApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:affiliate-api {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a API de afiliado para verificar os valores';

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
        
        $this->info("Testando API para usuário: {$user->name} (ID: {$user->id})");
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
        
        $this->info("=== DADOS DA API ===");
        $this->info("Indicações: {$indications}");
        $this->info("Total de depósitos: {$totalDeposits}");
        $this->info("Total de depositantes: {$totalDepositors}");
        $this->info("Total depositado: R$ " . number_format($totalDepositedAmount, 2, ',', '.'));
        $this->info("Carteira refer_rewards: R$ " . number_format($walletDefault->refer_rewards ?? 0, 2, ',', '.'));
        $this->newLine();
        
        // Verificar histórico de afiliado
        $this->info("=== HISTÓRICO DE AFILIADO ===");
        $revshareHistories = AffiliateHistory::where('inviter', $user->id)
            ->where('commission_type', 'revshare')
            ->get();
            
        $cpaHistories = AffiliateHistory::where('inviter', $user->id)
            ->where('commission_type', 'cpa')
            ->get();
        
        $totalRevshare = 0;
        $totalCpa = 0;
        
        foreach ($revshareHistories as $history) {
            $totalRevshare += $history->commission_paid;
            $this->info("RevShare - Usuário {$history->user_id}: R$ " . number_format($history->commission_paid, 2, ',', '.'));
        }
        
        foreach ($cpaHistories as $history) {
            $totalCpa += $history->commission_paid;
            $this->info("CPA - Usuário {$history->user_id}: R$ " . number_format($history->commission_paid, 2, ',', '.'));
        }
        
        $this->newLine();
        $this->info("=== RESUMO ===");
        $this->info("Total RevShare: R$ " . number_format($totalRevshare, 2, ',', '.'));
        $this->info("Total CPA: R$ " . number_format($totalCpa, 2, ',', '.'));
        $this->info("Total Geral: R$ " . number_format($totalRevshare + $totalCpa, 2, ',', '.'));
        $this->info("Valor na carteira: R$ " . number_format($walletDefault->refer_rewards ?? 0, 2, ',', '.'));
        
        if (($totalRevshare + $totalCpa) != ($walletDefault->refer_rewards ?? 0)) {
            $this->error("ERRO: Os valores não coincidem!");
            $this->error("Diferença: R$ " . number_format(($totalRevshare + $totalCpa) - ($walletDefault->refer_rewards ?? 0), 2, ',', '.'));
        } else {
            $this->info("SUCESSO: Os valores estão corretos!");
        }
    }
} 