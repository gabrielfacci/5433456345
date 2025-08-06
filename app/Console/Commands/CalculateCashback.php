<?php

namespace App\Console\Commands;

use App\Models\Cashback;
use App\Models\CashbackSetting;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateCashback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashback:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcula e gera registros de cashback para cada usuário';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $setting = CashbackSetting::singleton();

        // Verifica se o sistema está ativo
        if (!$setting->is_active) {
            $this->warn('Sistema de cashback está desativado.');
            return self::SUCCESS;
        }

        // Define range temporal
        [$start, $end] = $this->getPeriodRange($setting->periodicidade);

        $this->info("Calculando cashback de {$start->toDateTimeString()} até {$end->toDateTimeString()} | Percentual: {$setting->percentual}%");

        $totalProcessed = 0;

        User::chunk(200, function ($users) use ($start, $end, $setting, &$totalProcessed) {
            foreach ($users as $user) {
                // Calcula perdas líquidas: apostas - ganhos
                $apostas = Order::where('user_id', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('type', 'bet') // apostas
                    ->where('status', 1) // status 1 = confirmado
                    ->sum('amount');

                $ganhos = Order::where('user_id', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('type', 'win') // ganhos
                    ->where('status', 1) // status 1 = confirmado
                    ->sum('amount');

                $perdasLiquidas = $apostas - $ganhos;

                if ($perdasLiquidas <= 0) {
                    continue; // Usuário teve lucro ou empate, sem cashback
                }

                $valorCashback = round(($perdasLiquidas * ($setting->percentual / 100)), 2);

                // Aplica limites mínimo e máximo
                if ($valorCashback < $setting->min_cashback) {
                    continue;
                }
                
                if ($valorCashback > $setting->max_cashback) {
                    $valorCashback = $setting->max_cashback;
                }

                // Verifica se já existe cashback para este período
                $existingCashback = Cashback::where('user_id', $user->id)
                    ->where('periodo_inicio', $start)
                    ->where('periodo_fim', $end)
                    ->first();

                if ($existingCashback) {
                    continue; // Já processado
                }

                \DB::transaction(function () use ($user, $valorCashback, $start, $end, $setting) {
                    // Garante carteira de cashback
                    $cbWallet = \App\Models\CashbackWallet::firstOrCreate([
                        'user_id' => $user->id,
                    ], [
                        'balance' => 0,
                    ]);

                    $cbWallet->increment('balance', $valorCashback);

                    // Registra histórico
                    Cashback::create([
                        'user_id'       => $user->id,
                        'periodo_inicio'=> $start,
                        'periodo_fim'   => $end,
                        'valor'         => $valorCashback,
                        'percentual'    => $setting->percentual,
                        'status'        => 'pending',
                    ]);
                });

                $totalProcessed++;
                $this->info("Processado: {$user->name} - Apostas: R$ {$apostas} - Ganhos: R$ {$ganhos} - Perdas Líquidas: R$ {$perdasLiquidas} - Cashback: R$ {$valorCashback}");
            }
        });

        $this->info("Processo concluído. Total de usuários processados: {$totalProcessed}");

        return self::SUCCESS;
    }

    private function getPeriodRange(string $periodicidade): array
    {
        $end = Carbon::now();
        return match ($periodicidade) {
            'daily'   => [Carbon::now()->subDay(), $end],
            'weekly'  => [Carbon::now()->subWeek(), $end],
            'monthly' => [Carbon::now()->subMonth(), $end],
            default   => [Carbon::now()->subWeek(), $end],
        };
    }
} 