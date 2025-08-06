<?php

namespace App\Console\Commands;

use App\Models\DistributionSystem;
use App\Models\DistributionProcessControl;
use App\Models\Order;
use App\Models\GamesKey;
use App\Traits\Providers\PlayFiverTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessDistributionSystem extends Command
{
    use PlayFiverTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distribution:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa o sistema de distribuição automaticamente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $processControl = DistributionProcessControl::getCurrentProcess();
        
        if (!$processControl->canProcess()) {
            Log::info('Aguardando próxima execução', [
                'next_execution' => $processControl->next_execution,
                'status' => $processControl->status
            ]);
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            $processControl->startProcessing();
            
            $distribution = DistributionSystem::lockForUpdate()->first();
            
            if (!$distribution || !$distribution->ativo) {
                Log::info('Sistema de distribuição inativo ou não configurado');
                $processControl->finishProcessing(true);
                DB::commit();
                return self::SUCCESS;
            }

            if (!$distribution->start_cycle_at) {
                $distribution->update(['start_cycle_at' => now()]);
                Log::info('Iniciando novo ciclo de distribuição');
            }

            Log::info('ESTADO INICIAL', [
                'modo' => $distribution->modo,
                'total_arrecadado' => $distribution->total_arrecadado,
                'total_distribuido' => $distribution->total_distribuido,
                'meta_arrecadacao' => $distribution->meta_arrecadacao,
                'percentual_distribuicao' => $distribution->percentual_distribuicao,
            ]);

            if ($distribution->modo === 'arrecadacao') {
                $this->processArrecadacao($distribution);
            } elseif ($distribution->modo === 'distribuicao') {
                $this->processDistribuicao($distribution);
            }

            $processControl->finishProcessing(true);
            DB::commit();
            return self::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar distribuição: ' . $e->getMessage());
            $processControl->finishProcessing(false, $e->getMessage());
            throw $e;
        }
    }

    private function processArrecadacao($distribution)
    {
        $totalBets = Order::where('type', 'bet')
            ->where('created_at', '>=', $distribution->start_cycle_at)
            ->sum('amount');

        Log::info('ANTES DA MUDANÇA:', [
            'modo_atual' => $distribution->modo,
            'total_arrecadado' => $distribution->total_arrecadado,
            'total_distribuido' => $distribution->total_distribuido,
            'start_cycle_at' => $distribution->start_cycle_at
        ]);

        // Atualiza o total arrecadado para acompanhamento
        $distribution->total_arrecadado = $totalBets;
        $distribution->save();

        Log::info('💰 ARRECADAÇÃO EM ANDAMENTO', [
            'total_arrecadado' => $totalBets,
            'meta' => $distribution->meta_arrecadacao,
            'faltam' => $distribution->meta_arrecadacao - $totalBets,
            'start_cycle_at' => $distribution->start_cycle_at
        ]);

        if ($totalBets >= $distribution->meta_arrecadacao) {
            Log::info('🎯 META ATINGIDA - MUDANDO PARA DISTRIBUIÇÃO', [
                'total_arrecadado' => $totalBets,
                'meta' => $distribution->meta_arrecadacao,
                'rtp_novo' => $distribution->rtp_distribuicao
            ]);

            // Já estamos com lock no registro desde o início do comando
            $distribution->update([
                'modo' => 'distribuicao',
                'total_arrecadado' => $totalBets,
                'start_cycle_at' => now(),
            ]);

            // Atualiza o RTP somente após confirmar a mudança
            $this->updateRTP($distribution->rtp_distribuicao);

            Log::info('DEPOIS DA MUDANÇA:', [
                'modo_novo' => $distribution->fresh()->modo,
                'total_arrecadado_novo' => $distribution->fresh()->total_arrecadado,
                'total_distribuido_novo' => $distribution->fresh()->total_distribuido,
                'start_cycle_at' => $distribution->fresh()->start_cycle_at
            ]);
        }
    }

    private function processDistribuicao($distribution)
    {
        $totalWins = Order::where('type', 'win')
            ->where('created_at', '>=', $distribution->start_cycle_at)
            ->sum('amount');

        $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);

        $tempoMinimoEmSegundos = 600; // tempo mínimo para manter modo distribuição (ex: 10 minutos)
        $tempoDecorrido = now()->diffInSeconds($distribution->start_cycle_at);

        Log::info('🎲 DISTRIBUIÇÃO - ESTADO ATUAL', [
            'total_wins_ate_agora' => $totalWins,
            'valor_total_distribuir' => $valorDistribuir,
            'falta_distribuir' => $valorDistribuir - $totalWins,
            'tempo_decorrido' => $tempoDecorrido,
            'inicio_ciclo' => $distribution->start_cycle_at,
        ]);

        if ($totalWins >= $valorDistribuir) {
            Log::info('🎯 META DE DISTRIBUIÇÃO ATINGIDA - RESETANDO CICLO IMEDIATAMENTE');

            $distribution->update([
                'modo' => 'arrecadacao',
                'total_distribuido' => $totalWins,
                'start_cycle_at' => now(),
            ]);

            $this->updateRTP($distribution->rtp_arrecadacao);

            Log::info('CICLO COMPLETO - VOLTANDO PARA ARRECADAÇÃO', [
                'modo_novo' => $distribution->fresh()->modo,
                'total_distribuido_final' => $distribution->fresh()->total_distribuido,
                'rtp_novo' => $distribution->rtp_arrecadacao,
                'novo_inicio_ciclo' => $distribution->fresh()->start_cycle_at
            ]);
        } else {
            Log::info('🎰 AGUARDANDO META E TEMPO', [
                'total_atual' => $totalWins,
                'meta' => $valorDistribuir,
                'tempo_decorrido' => $tempoDecorrido,
                'tempo_minimo' => $tempoMinimoEmSegundos,
                'rtp' => $distribution->rtp_distribuicao
            ]);
        }
    }

    private function updateRTP($rtp)
    {
        try {
            $result = self::updateRTP($rtp);

            if ($result) {
                Log::info('RTP Atualizado com sucesso:', ['rtp' => $rtp]);
            } else {
                Log::error('Falha ao atualizar RTP:', ['rtp' => $rtp]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar RTP: ' . $e->getMessage());
        }
    }
}