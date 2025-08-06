<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DistributionSystem;
use App\Models\Order;
use Carbon\Carbon;

class ProcessDistribution extends Command
{
    protected $signature = 'distribution:process';
    protected $description = 'Processa a distribuição automática de ganhos';

    public function handle()
    {
        $distribution = DistributionSystem::first();
        
        if (!$distribution) {
            $this->error('Sistema de distribuição não encontrado!');
            return;
        }

        \Log::info('🔄 PROCESSANDO DISTRIBUIÇÃO', [
            'modo_atual' => $distribution->modo,
            'total_arrecadado' => $distribution->total_arrecadado,
            'total_distribuido' => $distribution->total_distribuido
        ]);

        if ($distribution->modo === 'arrecadacao') {
            $this->processArrecadacao($distribution);
        } elseif ($distribution->modo === 'distribuicao') {
            $this->processDistribuicao($distribution);
        }
    }

    private function processArrecadacao($distribution)
    {
        $totalBets = Order::where('type', 'bet')
            ->where('created_at', '>=', $distribution->start_cycle_at)
            ->sum('amount');

        $totalBetsFloat = (float) $totalBets;
        $metaArrecadacaoFloat = (float) $distribution->meta_arrecadacao;

        \Log::info('💰 PROCESSANDO ARRECADAÇÃO', [
            'total_apostas' => $totalBetsFloat,
            'meta_arrecadacao' => $metaArrecadacaoFloat,
            'atingiu_meta' => $totalBetsFloat >= $metaArrecadacaoFloat
        ]);

        if ($totalBetsFloat >= $metaArrecadacaoFloat) {
            \Log::info('🎉 ARRECADAÇÃO COMPLETA! Mudando para DISTRIBUIÇÃO');
            \Log::info('ANTES DA MUDANÇA:', [
                'modo_atual' => $distribution->modo,
                'total_arrecadado' => $distribution->total_arrecadado,
                'total_distribuido' => $distribution->total_distribuido
            ]);
            
            $distribution->update([
                'modo' => 'distribuicao',
                'start_cycle_at' => now(),
                'total_arrecadado' => $totalBetsFloat,
            ]);
            
            \Log::info('DEPOIS DA MUDANÇA:', [
                'modo_novo' => $distribution->fresh()->modo,
                'total_arrecadado_novo' => $distribution->fresh()->total_arrecadado,
                'total_distribuido_novo' => $distribution->fresh()->total_distribuido
            ]);

            // Atualizar RTP
            $setting = \App\Models\GamesKey::first();
            if ($setting) {
                try {
                    $response = \Illuminate\Support\Facades\Http::withOptions(['force_ip_resolve' => 'v4'])
                        ->put('https://api.playfivers.com/api/v2/agent', [
                            'agentToken' => $setting->playfiver_token,
                            'secretKey' => $setting->playfiver_secret,
                            'rtp' => $distribution->rtp_distribuicao,
                            'bonus_enable' => true,
                        ]);
                    
                    \Log::info('RTP Atualizado:', [
                        'rtp' => $distribution->rtp_distribuicao,
                        'response' => $response->json()
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Erro ao atualizar RTP:', ['error' => $e->getMessage()]);
                }
            }

            \Filament\Notifications\Notification::make()
                ->title('🎉 Arrecadação completa!')
                ->body('Sistema mudou para DISTRIBUIÇÃO')
                ->success()
                ->send();
        } else {
            $distribution->update([
                'total_arrecadado' => $totalBetsFloat,
            ]);
            
            \Log::info('💰 ARRECADAÇÃO EM ANDAMENTO', [
                'total_arrecadado' => $totalBetsFloat,
                'meta' => $metaArrecadacaoFloat,
                'faltam' => $metaArrecadacaoFloat - $totalBetsFloat
            ]);
        }
    }

    private function processDistribuicao($distribution)
    {
        $totalWins = Order::where('type', 'win')
            ->where('created_at', '>=', $distribution->start_cycle_at)
            ->sum('amount');

        $totalWinsFloat = (float) $totalWins;
        $valorDistribuirFloat = (float) $distribution->meta_distribuicao;

        \Log::info('🎁 PROCESSANDO DISTRIBUIÇÃO', [
            'total_ganhos' => $totalWinsFloat,
            'meta_distribuicao' => $valorDistribuirFloat,
            'atingiu_meta' => $totalWinsFloat >= $valorDistribuirFloat,
            'tipo_total_ganhos' => gettype($totalWinsFloat),
            'tipo_meta_distribuicao' => gettype($valorDistribuirFloat),
            'comparacao_exata' => $totalWinsFloat >= $valorDistribuirFloat ? 'SIM' : 'NÃO',
            'valor_exato_total_ganhos' => $totalWinsFloat,
            'valor_exato_meta_distribuicao' => $valorDistribuirFloat
        ]);

        if ($totalWinsFloat >= $valorDistribuirFloat) {
            \Log::info('🎉 DISTRIBUIÇÃO COMPLETA! Resetando ciclo');
            \Log::info('ANTES DA MUDANÇA:', [
                'modo_atual' => $distribution->modo,
                'total_arrecadado' => $distribution->total_arrecadado,
                'total_distribuido' => $distribution->total_distribuido
            ]);
            
            $resultadoUpdate = $distribution->update([
                'modo' => 'arrecadacao',
                'start_cycle_at' => now(),
                'total_arrecadado' => 0,
                'total_distribuido' => 0,
            ]);
            
            \Log::info('RESULTADO DO UPDATE:', [
                'update_sucesso' => $resultadoUpdate,
                'modo_novo' => $distribution->fresh()->modo,
                'total_arrecadado_novo' => $distribution->fresh()->total_arrecadado,
                'total_distribuido_novo' => $distribution->fresh()->total_distribuido
            ]);

            // Atualizar RTP
            $setting = \App\Models\GamesKey::first();
            if ($setting) {
                try {
                    $response = \Illuminate\Support\Facades\Http::withOptions(['force_ip_resolve' => 'v4'])
                        ->put('https://api.playfivers.com/api/v2/agent', [
                            'agentToken' => $setting->playfiver_token,
                            'secretKey' => $setting->playfiver_secret,
                            'rtp' => $distribution->rtp_arrecadacao,
                            'bonus_enable' => true,
                        ]);
                    
                    \Log::info('RTP Atualizado:', [
                        'rtp' => $distribution->rtp_arrecadacao,
                        'response' => $response->json()
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Erro ao atualizar RTP:', ['error' => $e->getMessage()]);
                }
            }

            \Filament\Notifications\Notification::make()
                ->title('🎉 Distribuição completa!')
                ->body('Ciclo resetado - Sistema voltou para ARRECADAÇÃO')
                ->success()
                ->send();
        } else {
            $distribution->update([
                'total_distribuido' => $totalWinsFloat,
            ]);
            
            \Log::info('🎁 DISTRIBUIÇÃO EM ANDAMENTO', [
                'total_distribuido' => $totalWinsFloat,
                'meta' => $valorDistribuirFloat,
                'faltam' => $valorDistribuirFloat - $totalWinsFloat
            ]);
        }
    }
} 