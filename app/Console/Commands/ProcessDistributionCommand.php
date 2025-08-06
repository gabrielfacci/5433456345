<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DistributionSystem;
use App\Models\Order;
use App\Models\GamesKey;
use Illuminate\Support\Facades\Http;

class ProcessDistributionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'distribution:process';

    /**
     * The console command description.
     */
    protected $description = 'Processa o sistema de distribuição automaticamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Processando sistema de distribuição...');

        $distribution = DistributionSystem::first();
        
        if (!$distribution) {
            $this->error('❌ Sistema de distribuição não encontrado!');
            return 1;
        }

        if (!$distribution->ativo) {
            $this->warn('⚠️ Sistema está inativo!');
            return 0;
        }

        // Inicia ciclo se necessário
        if (!$distribution->start_cycle_at) {
            $distribution->update(['start_cycle_at' => now()]);
            $this->info('✅ Ciclo iniciado em: ' . now());
        }

        $statusChanged = false;

        if ($distribution->modo === 'arrecadacao') {
            $totalBets = Order::where('type', 'bet')
                ->where('created_at', '>=', $distribution->start_cycle_at)
                ->sum('amount');

            $this->info("💰 Total de apostas: R$ " . number_format($totalBets, 2));
            
            $distribution->total_arrecadado = $totalBets;
            $distribution->save();

            if ($totalBets >= $distribution->meta_arrecadacao) {
                $this->info('🎉 META ATINGIDA! Mudando para modo DISTRIBUIÇÃO');
                
                $distribution->update([
                    'total_arrecadado' => 0,
                    'modo' => 'distribuicao',
                    'start_cycle_at' => now(),
                ]);

                // Atualizar RTP
                $this->updateRTP($distribution->rtp_distribuicao);
                $statusChanged = true;
            } else {
                $progresso = ($totalBets / $distribution->meta_arrecadacao) * 100;
                $falta = $distribution->meta_arrecadacao - $totalBets;
                $this->info("📊 Progresso: " . number_format($progresso, 1) . "%");
                $this->info("💸 Falta: R$ " . number_format($falta, 2));
            }
        } elseif ($distribution->modo === 'distribuicao') {
            $totalWins = Order::where('type', 'win')
                ->where('created_at', '>=', $distribution->start_cycle_at)
                ->sum('amount');

            $this->info("🎁 Total de ganhos: R$ " . number_format($totalWins, 2));
            
            $distribution->total_distribuido = $totalWins;
            $distribution->save();

            $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);

            if ($totalWins >= $valorDistribuir) {
                $this->info('🎉 DISTRIBUIÇÃO COMPLETA! Mudando para modo ARRECADAÇÃO');
                
                $distribution->update([
                    'total_distribuido' => 0,
                    'modo' => 'arrecadacao',
                    'start_cycle_at' => now(),
                ]);

                // Atualizar RTP
                $this->updateRTP($distribution->rtp_arrecadacao);
                $statusChanged = true;
            } else {
                $progresso = ($totalWins / $valorDistribuir) * 100;
                $falta = $valorDistribuir - $totalWins;
                $this->info("📊 Progresso: " . number_format($progresso, 1) . "%");
                $this->info("🎁 Falta: R$ " . number_format($falta, 2));
            }
        }

        $this->info('✅ Processamento concluído!');
        $this->info('- Modo: ' . $distribution->modo);
        $this->info('- Arrecadado: R$ ' . number_format($distribution->total_arrecadado, 2));
        $this->info('- Distribuído: R$ ' . number_format($distribution->total_distribuido, 2));
        $this->info('- Mudou: ' . ($statusChanged ? 'SIM' : 'NÃO'));

        return 0;
    }

    private function updateRTP($rtp)
    {
        $setting = GamesKey::first();
        if ($setting) {
            try {
                Http::withOptions(['force_ip_resolve' => 'v4'])
                    ->put('https://api.playfivers.com/api/v2/agent', [
                        'agentToken' => $setting->playfiver_token,
                        'secretKey' => $setting->playfiver_secret,
                        'rtp' => $rtp,
                        'bonus_enable' => true,
                    ]);
                $this->info("✅ RTP atualizado para: {$rtp}%");
            } catch (\Exception $e) {
                $this->error("❌ Erro ao atualizar RTP: " . $e->getMessage());
            }
        }
    }
} 