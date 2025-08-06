<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\AffiliateHistory;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
//use App\Traits\Providers\PlayConnectTrait;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '15s';

    protected static bool $isLazy = true;

    /**
     * @return array|Stat[]
     */
    protected function getStats(): array
    {
        
         $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $setting = \Helper::getSetting();
        $dataAtual = Carbon::now();
        $depositQuery = Deposit::query();
        $withdrawalQuery = Withdrawal::query();

        if(empty($startDate) && empty($endDate)) {
            $depositQuery->whereMonth('created_at', Carbon::now()->month);
        }else{
            $depositQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Executa a consulta para obter a soma dos depósitos para o mês atual
        $sumDepositMonth = $depositQuery
            ->where('status', 1)
            ->sum('amount');
        $sumDepositPendenteMonth = $depositQuery
        
            ->sum('amount');


        $withdrawalQuery->where('status', 1);

        if(empty($startDate) && empty($endDate)) {
            $withdrawalQuery->whereMonth('created_at', Carbon::now()->month);
        }else{
            $withdrawalQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $sumWithdrawalMonth = $withdrawalQuery->sum('amount');
        $revshare = AffiliateHistory::where('commission_type', 'revshare')->sum('commission_paid');
        
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $totalApostas = Order::whereIn('type', ['bet', 'loss'])->sum('amount');
        $totalWins = Order::where('type', 'win')->sum('amount');
        $today = Carbon::today();

        $totalWonLast7Days = $totalWins;
        $totalLoseLast7Days = $totalApostas;

        $saldodosplayers = DB::table('users')->join('wallets', function ($join) {$join->on('users.id', '=', 'wallets.user_id')
             ->where('users.id', '!=', 1)
             ->where('users.is_demo_agent', 0);
    })
             ->where('wallets.balance_withdrawal', '>=', 0)
             ->sum('wallets.balance_withdrawal');
        
        
        $totalDepositedGeralMonth = DB::table('deposits')
             ->where('status', '1')
           // Filtrar apenas os depósitos aprovados
            ->sum('amount');
        $totalDepositedToday = DB::table('deposits')
            ->whereDate('created_at', $today)
            ->where('status', '1') // Filtrar apenas os depósitos aprovados
            ->sum('amount');
        $totalDepositedPendenteMonth = DB::table('deposits')
            ->where('status', '0') // Filtrar apenas os depósitos pendentes
            ->sum('amount');
        $totalDepositedPendenteToday = DB::table('deposits')
          ->whereDate('created_at', $today)
            ->where('status', '0') 
            ->sum('amount');
        $totalsacadoPendenteToday = DB::table('withdrawals')
            ->whereDate('created_at', $today)
            ->where('status', '0') 
            ->sum('amount');
        $totalsacadoToday = DB::table('withdrawals')
            ->whereDate('created_at', $today)
            ->where('status', '1') // Filtrar apenas os depósitos aprovados
            ->sum('amount');
        $totalsacadoMonth = DB::table('withdrawals')
            ->where('status', '1') // Filtrar apenas os depósitos aprovados
            ->sum('amount');
        $totalsacadoPendenteMonth = DB::table('withdrawals')
            ->where('status', '0') // Filtrar apenas os depósitos pendentes
            ->sum('amount');
$totalReferRewardsLast7Days = DB::table('wallets')
    ->where('refer_rewards', '>=', 0) // Adicione esta linha para filtrar apenas os valores maiores ou iguais a 20
    ->sum('refer_rewards');

$depositCounts = DB::table('deposits')
    ->select('user_id', DB::raw('count(*) as deposit_count'))
    ->where('status', '1')
    ->groupBy('user_id')
    ->get();


$usersWithSingleDeposit = $depositCounts->filter(function ($item) {
    return $item->deposit_count === 1;
});

$numberOfUsersWithSingleDeposit = $usersWithSingleDeposit->count();

$usersWithTwoDeposits = $depositCounts->filter(function ($item) {
    return $item->deposit_count === 2;
});
$numberOfUsersWithTwoDeposits = $usersWithTwoDeposits->count();

$usersWithThreeDeposits = $depositCounts->filter(function ($item) {
    return $item->deposit_count === 3;
});
$numberOfUsersWithThreeDeposits = $usersWithThreeDeposits->count();

$usersWithFourOrMoreDeposits = $depositCounts->filter(function ($item) {
    return $item->deposit_count >= 4;
});
$numberOfUsersWithFourOrMoreDeposits = $usersWithFourOrMoreDeposits->count();



        return [
            Stat::make('Total Depositos Aprovados (Mês)', \Helper::amountFormatDecimal($sumDepositMonth))
                ->description('Total de Depositos Aprovados (Mês)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make('Total de Depositos Pendentes (Mês)', \Helper::amountFormatDecimal($totalDepositedPendenteMonth))
                ->description('Total de Depositos Pendentes (Mês)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make('Total de Saques Aprovados (Mês)', \Helper::amountFormatDecimal($totalsacadoMonth))
                ->description('Total de Saques Aprovados (Mês)')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('primary'),
            Stat::make('Total de Saques Pendentes (Mês)', \Helper::amountFormatDecimal($totalsacadoPendenteMonth))
                ->description('Total de Saques Pendentes (Mês)')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('primary'),
            Stat::make('Revshare', \Helper::amountFormatDecimal($revshare))
                ->description('Ganhos da Plataforma')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Total Usuários', User::count())
                ->description('Novos usuários')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Total Depósitos Aprovados (Hoje)', \Helper::amountFormatDecimal($totalDepositedToday))
                ->description('Total Depósitos Aprovados (Hoje)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Total Depositos Pendentes Hoje', \Helper::amountFormatDecimal($totalDepositedPendenteToday))
                ->description('Total Depositos Pendentes (Hoje)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Total Saques Aprovados Hoje', \Helper::amountFormatDecimal($totalsacadoToday))
                ->description('Total Saques Aprovados hoje')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Total Saques Pendentes Hoje', \Helper::amountFormatDecimal($totalsacadoPendenteToday))
                ->description('Total Saques Pendentes hoje')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Saldo para saque (JOGADORES)', \Helper::amountFormatDecimal($saldodosplayers))
                ->description('Disponível para saque (JOGADORES)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Ganhos Afiliados a pagar', \Helper::amountFormatDecimal($totalReferRewardsLast7Days))
                ->description('Ganhos dos Afiliado a pagar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Pessoas Que Depositaram 1 Vez', $numberOfUsersWithSingleDeposit)
                ->description('Pessoas Que Depositaram 1 Vez')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Pessoas Que Depositaram 2 Vezes', $numberOfUsersWithTwoDeposits)
                ->description('Pessoas Que Depositaram 2 Vezes')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Pessoas Que Depositaram 3 Vezes', $numberOfUsersWithThreeDeposits)
                ->description('Pessoas Que Depositaram 3 Vezes')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Pessoas Que Depositaram 4 Vezes ou mais', $numberOfUsersWithFourOrMoreDeposits)
                ->description('Pessoas Que Depositaram 4 Vezes ou mais')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
                Stat::make('Total Ganhos', \Helper::amountFormatDecimal($totalWonLast7Days))
                ->description('Ganhos dos usuários')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3]),
            Stat::make('Total Perdas', \Helper::amountFormatDecimal($totalLoseLast7Days))
                ->description('Perdas dos usuários')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart([7,3,4,5,6,3,5,3])
        ];


        /*$gateway = GamesKey::first();
        if(!empty($gateway->playconnect_token)) {
            $balance_api = PlayConnectTrait::BalancePlayConnect();

            Stat::make('Saldo na API', \Helper::amountFormatDecimal($balance_api))
                ->description('Saldo na API')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary');

          

        }*/

    
    }
    
    
    /**
     * @return bool
     */
    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}