<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DistributionSystem;
use App\Models\Order;
use App\Models\GamesKey;
use Illuminate\Support\Facades\Http;

class ProcessDistribution30s extends Command
{
    protected $signature = 'distribution:process-30s';
    protected $description = 'Processa sistema de distribuição a cada 30 segundos';

    public function handle()
    {
        $this->info('🔄 Processando sistema de distribuição (30s)...');
        $this->info('⏰ Timestamp: ' . now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'));

        $distribution = DistributionSystem::first();

        if (!$distribution) {
            $this->error('❌ Sistema não encontrado!');
            return 1;
        }

        if (!$distribution->ativo) {
            $this->warn('⚠️ Sistema inativo!');
            return 0;
        }

        // Inicia ciclo se necessário
        if (!$distribution->start_cycle_at) {
            $distribution->update(['start_cycle_at' => now()]);
            $this->info('✅ Ciclo iniciado em: ' . now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'));
        }

        $statusChanged = false;

        if ($distribution->modo === 'arrecadacao') {
            $totalBets = Order::where('type', 'bet')
                ->where('created_at', '>=', $distribution->start_cycle_at)
                ->sum('amount');

            $this->info('💰 Total de apostas: R$ ' . number_format($totalBets, 2));
            $distribution->total_arrecadado = $totalBets;
            $distribution->save();

            $metaFloat = (float) $distribution->meta_arrecadacao;
            if ($totalBets >= $metaFloat && $distribution->modo === 'arrecadacao') {
                $this->info('🎉 META ATINGIDA! Mudando para modo DISTRIBUIÇÃO');
                $distribution->update([
                    'modo' => 'distribuicao',
                    'start_cycle_at' => now(),
                    'total_distribuido' => 0,
                ]);
                $this->updateRTP($distribution->rtp_distribuicao);
                \Log::info('MUDOU PARA DISTRIBUICAO', ['rtp' => $distribution->rtp_distribuicao]);
                $statusChanged = true;
                $this->info('✅ Processamento concluído!');
                $this->info('- Modo: ' . $distribution->modo);
                $this->info('- Arrecadado: R$ ' . number_format($distribution->total_arrecadado, 2));
                $this->info('- Distribuído: R$ ' . number_format($distribution->total_distribuido, 2));
                $this->info('- Mudou: ' . ($statusChanged ? 'SIM' : 'NÃO'));
                $this->info('- Próxima execução em 30 segundos');
                return 0;
            } else {
                $progresso = ($totalBets / $metaFloat) * 100;
                $falta = $metaFloat - $totalBets;
                $this->info('📊 Progresso: ' . number_format($progresso, 1) . '%');
                $this->info('💸 Falta: R$ ' . number_format($falta, 2));
            }
        } elseif ($distribution->modo === 'distribuicao') {
            $totalWins = Order::where('type', 'win')
                ->where('created_at', '>=', $distribution->start_cycle_at)
                ->sum('amount');

            $this->info('🎁 Total de ganhos: R$ ' . number_format($totalWins, 2));
            $distribution->total_distribuido = $totalWins;
            $distribution->save();

            $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);
            // Só volta para arrecadação se realmente atingir a meta de distribuição
            if ($valorDistribuir > 0 && $totalWins >= $valorDistribuir && $distribution->modo === 'distribuicao') {
                $this->info('🎉 DISTRIBUIÇÃO COMPLETA! Resetando ciclo para ARRECADAÇÃO');
                $distribution->update([
                    'modo' => 'arrecadacao',
                    'start_cycle_at' => now(),
                    'total_arrecadado' => 0,
                    'total_distribuido' => 0,
                ]);
                $this->updateRTP($distribution->rtp_arrecadacao);
                \Log::info('MUDOU PARA ARRECADACAO', ['rtp' => $distribution->rtp_arrecadacao]);
                $statusChanged = true;
                $this->info('✅ Processamento concluído!');
                $this->info('- Modo: ' . $distribution->modo);
                $this->info('- Arrecadado: R$ ' . number_format($distribution->total_arrecadado, 2));
                $this->info('- Distribuído: R$ ' . number_format($distribution->total_distribuido, 2));
                $this->info('- Mudou: ' . ($statusChanged ? 'SIM' : 'NÃO'));
                $this->info('- Próxima execução em 30 segundos');
                return 0;
            }
            // Caso contrário, permanece em distribuição aguardando os ganhos
            $progresso = ($valorDistribuir > 0) ? ($totalWins / $valorDistribuir) * 100 : 0;
            $falta = $valorDistribuir - $totalWins;
            $this->info('📊 Progresso: ' . number_format($progresso, 1) . '%');
            $this->info('🎁 Falta: R$ ' . number_format($falta, 2));
        }

        $this->info('✅ Processamento concluído!');
        $this->info('- Modo: ' . $distribution->modo);
        $this->info('- Arrecadado: R$ ' . number_format($distribution->total_arrecadado, 2));
        $this->info('- Distribuído: R$ ' . number_format($distribution->total_distribuido, 2));
        $this->info('- Mudou: ' . ($statusChanged ? 'SIM' : 'NÃO'));
        $this->info('- Próxima execução em 30 segundos');

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