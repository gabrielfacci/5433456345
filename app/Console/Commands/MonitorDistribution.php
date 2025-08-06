<?php

namespace App\Console\Commands;

use App\Models\DistributionSystem;
use App\Models\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MonitorDistribution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distribution:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitora o sistema de distribuição em tempo real';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $distribution = DistributionSystem::first();
        if (!$distribution) {
            $this->error('❌ Sistema de distribuição não configurado!');
            return self::FAILURE;
        }

        // Header do monitoramento
        $this->line('');
        $this->line('🎯 ' . str_repeat('=', 50));
        $this->line('   MONITOR DO SISTEMA DE DISTRIBUIÇÃO');
        $this->line('🎯 ' . str_repeat('=', 50));
        $this->line('');

        // Status geral
        $this->displayStatus($distribution);
        
        // Estatísticas do ciclo atual
        $this->displayCycleStats($distribution);
        
        // Histórico recente
        $this->displayRecentActivity($distribution);
        
        // Próximos passos
        $this->displayNextSteps($distribution);
        
        return self::SUCCESS;
    }

    private function displayStatus(DistributionSystem $distribution): void
    {
        $status = $distribution->ativo ? '✅ ATIVO' : '❌ INATIVO';
        $modo = $distribution->modo === 'arrecadacao' ? '💰 ARRECADAÇÃO' : '🎁 DISTRIBUIÇÃO';
        $rtp = $distribution->modo === 'arrecadacao' ? $distribution->rtp_arrecadacao : $distribution->rtp_distribuicao;

        $this->line("📊 <info>Status:</info> {$status}");
        $this->line("🎮 <info>Modo:</info> {$modo}");
        $this->line("📈 <info>RTP Atual:</info> {$rtp}%");
        $this->line("📅 <info>Início do Ciclo:</info> " . $distribution->start_cycle_at?->format('d/m/Y H:i:s'));
        $this->line('');
    }

    private function displayCycleStats(DistributionSystem $distribution): void
    {
        $this->line('📈 <comment>ESTATÍSTICAS DO CICLO ATUAL</comment>');
        $this->line(str_repeat('-', 40));

        if ($distribution->modo === 'arrecadacao') {
            $progress = $distribution->meta_arrecadacao > 0 
                ? ($distribution->total_arrecadado / $distribution->meta_arrecadacao) * 100 
                : 0;
            
            $this->line("💸 <info>Total Arrecadado:</info> R$ " . number_format($distribution->total_arrecadado, 2, ',', '.'));
            $this->line("🎯 <info>Meta:</info> R$ " . number_format($distribution->meta_arrecadacao, 2, ',', '.'));
            $this->line("📊 <info>Progresso:</info> " . number_format($progress, 1) . "%");
            
            $faltam = $distribution->meta_arrecadacao - $distribution->total_arrecadado;
            if ($faltam > 0) {
                $this->line("⏳ <info>Faltam:</info> R$ " . number_format($faltam, 2, ',', '.'));
            }
        } else {
            $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);
            $progress = $valorDistribuir > 0 
                ? ($distribution->total_distribuido / $valorDistribuir) * 100 
                : 0;
            
            $this->line("🎁 <info>Total Distribuído:</info> R$ " . number_format($distribution->total_distribuido, 2, ',', '.'));
            $this->line("🎯 <info>Meta Distribuir:</info> R$ " . number_format($valorDistribuir, 2, ',', '.'));
            $this->line("📊 <info>Progresso:</info> " . number_format($progress, 1) . "%");
            
            $faltam = $valorDistribuir - $distribution->total_distribuido;
            if ($faltam > 0) {
                $this->line("⏳ <info>Faltam:</info> R$ " . number_format($faltam, 2, ',', '.'));
            }
        }
        $this->line('');
    }

    private function displayRecentActivity(DistributionSystem $distribution): void
    {
        $this->line('🎮 <comment>ATIVIDADE RECENTE (Últimas 24h)</comment>');
        $this->line(str_repeat('-', 40));

        $yesterday = now()->subDay();
        $bets = Order::where('type', 'bet')
            ->where('created_at', '>=', $yesterday)
            ->count();
        $wins = Order::where('type', 'win')
            ->where('created_at', '>=', $yesterday)
            ->count();
        $totalBet = Order::where('type', 'bet')
            ->where('created_at', '>=', $yesterday)
            ->sum('amount');
        $totalWin = Order::where('type', 'win')
            ->where('created_at', '>=', $yesterday)
            ->sum('amount');

        $this->line("📊 <info>Apostas:</info> {$bets} (R$ " . number_format($totalBet, 2, ',', '.') . ")");
        $this->line("🎁 <info>Ganhos:</info> {$wins} (R$ " . number_format($totalWin, 2, ',', '.') . ")");
        
        $profit = $totalBet - $totalWin;
        $profitLabel = $profit >= 0 ? 'Lucro' : 'Prejuízo';
        $profitIcon = $profit >= 0 ? '💰' : '💸';
        $this->line("{$profitIcon} <info>{$profitLabel}:</info> R$ " . number_format(abs($profit), 2, ',', '.'));
        $this->line('');
    }

    private function displayNextSteps(DistributionSystem $distribution): void
    {
        $this->line('🚀 <comment>PRÓXIMOS PASSOS</comment>');
        $this->line(str_repeat('-', 40));

        if ($distribution->modo === 'arrecadacao') {
            $faltam = $distribution->meta_arrecadacao - $distribution->total_arrecadado;
            if ($faltam > 0) {
                $this->line("• Arrecadar mais R$ " . number_format($faltam, 2, ',', '.'));
                $this->line("• Quando atingir a meta → Muda para DISTRIBUIÇÃO");
                $this->line("• RTP será alterado para {$distribution->rtp_distribuicao}%");
            } else {
                $this->line("• Meta já atingida! Processamento mudará para DISTRIBUIÇÃO");
            }
        } else {
            $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);
            $faltam = $valorDistribuir - $distribution->total_distribuido;
            if ($faltam > 0) {
                $this->line("• Distribuir mais R$ " . number_format($faltam, 2, ',', '.'));
                $this->line("• Quando atingir a meta → Muda para ARRECADAÇÃO");
                $this->line("• RTP será alterado para {$distribution->rtp_arrecadacao}%");
            } else {
                $this->line("• Meta já atingida! Processamento mudará para ARRECADAÇÃO");
            }
        }

        $this->line('');
        $this->line("⏰ <info>Próxima execução automática:</info> A cada 5 minutos");
        $this->line("🔧 <info>Processar manualmente:</info> php artisan distribution:process");
        $this->line('');
    }
} 