<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\AffiliateWithdraw;

class TestAffiliateWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:affiliate-withdrawal {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o modal de saque de afiliado';

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

        $this->info("=== TESTE DO MODAL DE SAQUE DE AFILIADO ===");
        $this->info("Usuário: {$user->name} (ID: {$user->id})");
        
        // Verificar carteira
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            $this->error("Carteira não encontrada!");
            return;
        }
        
        $this->info("Saldo de RevShare: R$ " . number_format($wallet->refer_rewards, 2, ',', '.'));
        
        // Verificar configurações
        $settings = Setting::where('id', 1)->first();
        if (!$settings) {
            $this->error("Configurações não encontradas!");
            return;
        }
        
        $this->info("Configurações de saque:");
        $this->info("- Min saque: R$ " . number_format($settings->min_withdrawal, 2, ',', '.'));
        $this->info("- Max saque: R$ " . number_format($settings->max_withdrawal, 2, ',', '.'));
        $this->info("- Limite de saques: " . ($settings->withdrawal_limit ?? 'Sem limite'));
        $this->info("- Período: " . ($settings->withdrawal_period ?? 'Sem período'));
        
        // Verificar saques existentes
        $withdrawals = AffiliateWithdraw::where('user_id', $user->id)->get();
        $this->info("Saques existentes: " . $withdrawals->count());
        
        foreach ($withdrawals as $withdrawal) {
            $status = $withdrawal->status == 0 ? 'Pendente' : ($withdrawal->status == 1 ? 'Aprovado' : 'Cancelado');
            $this->info("- R$ " . number_format($withdrawal->amount, 2, ',', '.') . " ({$status})");
        }
        
        // Testar validações
        $this->info("\n=== TESTE DE VALIDAÇÕES ===");
        
        // Teste 1: Valor menor que mínimo
        $testAmount1 = $settings->min_withdrawal - 1;
        $this->info("Teste 1 - Valor menor que mínimo (R$ {$testAmount1}):");
        $this->testValidation($user, $testAmount1, 'document', '12345678901');
        
        // Teste 2: Valor maior que máximo
        $testAmount2 = $settings->max_withdrawal + 1;
        $this->info("Teste 2 - Valor maior que máximo (R$ {$testAmount2}):");
        $this->testValidation($user, $testAmount2, 'document', '12345678901');
        
        // Teste 3: Valor maior que saldo
        $testAmount3 = $wallet->refer_rewards + 1;
        $this->info("Teste 3 - Valor maior que saldo (R$ {$testAmount3}):");
        $this->testValidation($user, $testAmount3, 'document', '12345678901');
        
        // Teste 4: Valor válido
        $testAmount4 = min($settings->min_withdrawal, $wallet->refer_rewards);
        $this->info("Teste 4 - Valor válido (R$ {$testAmount4}):");
        $this->testValidation($user, $testAmount4, 'document', '12345678901');
        
        $this->info("\n=== FIM DOS TESTES ===");
    }
    
    private function testValidation($user, $amount, $pixType, $pixKey)
    {
        $settings = Setting::where('id', 1)->first();
        
        // Verificar limites de configuração
        if ($amount < $settings->min_withdrawal) {
            $this->error("  ❌ Valor menor que mínimo permitido");
            return false;
        }
        
        if ($amount > $settings->max_withdrawal && $settings->max_withdrawal > 0) {
            $this->error("  ❌ Valor maior que máximo permitido");
            return false;
        }
        
        // Verificar saldo
        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($amount > $wallet->refer_rewards) {
            $this->error("  ❌ Saldo insuficiente");
            return false;
        }
        
        // Verificar limites de período
        if ($settings->withdrawal_limit && $settings->withdrawal_period) {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();

            switch ($settings->withdrawal_period) {
                case 'weekly':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    break;
                case 'monthly':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    break;
                case 'yearly':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    break;
            }

            $withdrawalCount = AffiliateWithdraw::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            if ($withdrawalCount >= $settings->withdrawal_limit) {
                $this->error("  ❌ Limite de saques atingido para o período");
                return false;
            }
        }
        
        $this->info("  ✅ Validação passou");
        return true;
    }
} 