<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Wallet;
use App\Models\AffiliateHistory;
use Illuminate\Support\Facades\DB;

class FixAffiliateWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:affiliate-wallet {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige os valores da carteira de afiliado recalculando as comissões';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuário não encontrado!");
                return;
            }
            $this->fixUserWallet($user);
        } else {
            $this->info("Corrigindo carteiras de todos os afiliados...");
            $affiliates = User::whereHas('roles', function($query) {
                $query->where('name', 'afiliado');
            })->get();
            
            $bar = $this->output->createProgressBar($affiliates->count());
            $bar->start();
            
            foreach ($affiliates as $affiliate) {
                $this->fixUserWallet($affiliate);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
        }
        
        $this->info("Processo concluído!");
    }
    
    private function fixUserWallet($user)
    {
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            $this->warn("Carteira não encontrada para o usuário {$user->id}");
            return;
        }
        
        // Buscar histórico de RevShare onde o usuário é o afiliado
        $revshareHistories = AffiliateHistory::where('inviter', $user->id)
            ->where('commission_type', 'revshare')
            ->get();
            
        // Buscar histórico de CPA onde o usuário é o afiliado
        $cpaHistories = AffiliateHistory::where('inviter', $user->id)
            ->where('commission_type', 'cpa')
            ->get();
        
        $totalRevshare = 0;
        $totalCpa = 0;
        
        // Calcular total de RevShare
        foreach ($revshareHistories as $history) {
            $totalRevshare += $history->commission_paid;
        }
        
        // Calcular total de CPA
        foreach ($cpaHistories as $history) {
            $totalCpa += $history->commission_paid;
        }
        
        $totalCommission = $totalRevshare + $totalCpa;
        
        // Atualizar carteira
        $oldValue = $wallet->refer_rewards;
        $wallet->update(['refer_rewards' => $totalCommission]);
        
        $this->info("Usuário {$user->id} ({$user->name}):");
        $this->info("  - Valor anterior: R$ " . number_format($oldValue, 2, ',', '.'));
        $this->info("  - Valor corrigido: R$ " . number_format($totalCommission, 2, ',', '.'));
        $this->info("  - RevShare: R$ " . number_format($totalRevshare, 2, ',', '.'));
        $this->info("  - CPA: R$ " . number_format($totalCpa, 2, ',', '.'));
        $this->info("  - Diferença: R$ " . number_format($totalCommission - $oldValue, 2, ',', '.'));
        $this->newLine();
    }
} 