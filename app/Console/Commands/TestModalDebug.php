<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestModalDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:modal-debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug do modal de saque';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== DEBUG DO MODAL DE SAQUE ===");
        
        $this->info("1. Verifique se o modal está sendo inicializado:");
        $this->info("   - Abra o console do navegador (F12)");
        $this->info("   - Vá para a página de afiliado");
        $this->info("   - Clique no botão 'Solicitar saque'");
        $this->info("   - Verifique se aparecem os logs no console");
        
        $this->info("\n2. Logs esperados no console:");
        $this->info("   - 'Abrindo modal de saque...'");
        $this->info("   - 'Modal object: [object]'");
        $this->info("   - 'Wallet: [object]'");
        $this->info("   - 'Form data: [object]'");
        
        $this->info("\n3. Se aparecer erro 'Modal não foi inicializado corretamente':");
        $this->info("   - O elemento do modal não foi encontrado");
        $this->info("   - Verifique se o HTML do modal está correto");
        $this->info("   - Verifique se o Flowbite está carregado");
        
        $this->info("\n4. Verifique se o modal aparece:");
        $this->info("   - O modal deve aparecer no centro da tela");
        $this->info("   - Deve ter um fundo escuro");
        $this->info("   - Deve ter um botão X para fechar");
        
        $this->info("\n5. Verifique se o formulário funciona:");
        $this->info("   - O campo 'Valor do Saque' deve estar preenchido com o saldo");
        $this->info("   - Os campos 'Chave Pix' e 'Tipo de Chave' devem estar vazios");
        $this->info("   - O botão 'Solicitar agora' deve estar habilitado");
        
        $this->info("\n6. Teste o envio do formulário:");
        $this->info("   - Preencha os campos obrigatórios");
        $this->info("   - Clique em 'Solicitar agora'");
        $this->info("   - Verifique se aparece mensagem de sucesso");
        
        $this->info("\n=== FIM DO DEBUG ===");
    }
} 