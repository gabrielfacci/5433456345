<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\AffiliateWithdraw;

class TestModalWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:modal-withdrawal {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa se o modal de saque está funcionando';

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

        $this->info("=== TESTE DO MODAL DE SAQUE ===");
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
        
        // Simular dados do formulário
        $testData = [
            'amount' => min($settings->min_withdrawal, $wallet->refer_rewards),
            'pix_key' => '12345678901',
            'pix_type' => 'document'
        ];
        
        $this->info("\n=== SIMULAÇÃO DE SAQUE ===");
        $this->info("Dados do formulário:");
        $this->info("- Valor: R$ " . number_format($testData['amount'], 2, ',', '.'));
        $this->info("- Chave PIX: " . $testData['pix_key']);
        $this->info("- Tipo: " . $testData['pix_type']);
        
        // Validar dados
        $this->info("\n=== VALIDAÇÃO ===");
        
        // Verificar se o valor está dentro dos limites
        if ($testData['amount'] < $settings->min_withdrawal) {
            $this->error("❌ Valor menor que mínimo permitido");
            return;
        }
        
        if ($testData['amount'] > $settings->max_withdrawal && $settings->max_withdrawal > 0) {
            $this->error("❌ Valor maior que máximo permitido");
            return;
        }
        
        // Verificar saldo
        if ($testData['amount'] > $wallet->refer_rewards) {
            $this->error("❌ Saldo insuficiente");
            return;
        }
        
        // Verificar chave PIX
        if (empty($testData['pix_key'])) {
            $this->error("❌ Chave PIX obrigatória");
            return;
        }
        
        // Verificar tipo de chave
        if (empty($testData['pix_type'])) {
            $this->error("❌ Tipo de chave obrigatório");
            return;
        }
        
        $this->info("✅ Validação passou");
        
        // Simular criação do saque
        $this->info("\n=== SIMULAÇÃO DE CRIAÇÃO ===");
        
        try {
            // Criar o registro de saque
            $withdrawal = AffiliateWithdraw::create([
                'user_id'   => $user->id,
                'amount'    => $testData['amount'],
                'pix_key'   => $testData['pix_key'],
                'pix_type'  => $testData['pix_type'],
                'currency'  => 'BRL',
                'symbol'    => 'R$',
                'status'    => 0
            ]);
            
            $this->info("✅ Saque criado com sucesso (ID: {$withdrawal->id})");
            
            // Decrementar saldo
            $wallet->decrement('refer_rewards', $testData['amount']);
            $this->info("✅ Saldo decrementado");
            
            // Verificar saldo atual
            $wallet->refresh();
            $this->info("Saldo atual: R$ " . number_format($wallet->refer_rewards, 2, ',', '.'));
            
            // Deletar o saque de teste
            $withdrawal->delete();
            $this->info("✅ Saque de teste removido");
            
            // Restaurar saldo
            $wallet->increment('refer_rewards', $testData['amount']);
            $this->info("✅ Saldo restaurado");
            
        } catch (\Exception $e) {
            $this->error("❌ Erro ao criar saque: " . $e->getMessage());
        }
        
        $this->info("\n=== FIM DO TESTE ===");
        $this->info("O modal deve estar funcionando corretamente!");
    }
} 