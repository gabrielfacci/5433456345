<?php

namespace App\Filament\Admin\Widgets;

use App\Helpers\Core as Helper;
use App\Models\AffiliateHistory;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsUserDetailOverview extends BaseWidget
{
    public User $record;

    public function mount($record)
    {
       $this->record = $record;
    }

    /**
     * @return array|Stat[]
     */
    protected function getStats(): array
    {
        // Verificar se o record existe
        if (!$this->record) {
            return [
                Stat::make('Erro', 'Record não encontrado')
                    ->description('Usuário não encontrado')
                    ->color('danger'),
            ];
        }

        // Estatísticas gerais do usuário
        $totalGanhos = Order::where('user_id', $this->record->id)->where('type', 'win')->sum('amount');
        $totalPerdas = Order::where('user_id', $this->record->id)->where('type', ['bet', 'loss'])->sum('amount');
        
        // Estatísticas de afiliado
        $totalAfiliados = AffiliateHistory::where('inviter', $this->record->id)->sum('commission_paid');
        $totalCpa = AffiliateHistory::where('inviter', $this->record->id)->where('commission_type', 'cpa')->sum('commission_paid');
        $totalRevshare = AffiliateHistory::where('inviter', $this->record->id)->where('commission_type', 'revshare')->sum('commission_paid');
        
        // Informações da carteira
        $wallet = Wallet::where('user_id', $this->record->id)->first();
        $saldoCarteira = $wallet ? $wallet->balance : 0;
        $saldoAfiliado = $wallet ? $wallet->refer_rewards : 0;
        
        // Contadores
        $totalIndicacoes = User::where('inviter', $this->record->id)->count();
        $totalDepositosIndicados = AffiliateHistory::where('inviter', $this->record->id)->sum('deposited_amount');

        return [
            // Estatísticas de jogo
            Stat::make('Total de Ganhos', Helper::amountFormatDecimal(Helper::formatNumber($totalGanhos)))
                ->description('Total de Ganhos na plataforma')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Total de Perdas', Helper::amountFormatDecimal(Helper::formatNumber($totalPerdas)))
                ->description('Total de Perdas na plataforma')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
                
            Stat::make('Saldo da Carteira', Helper::amountFormatDecimal(Helper::formatNumber($saldoCarteira)))
                ->description('Saldo atual da carteira principal')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
                
            // Estatísticas de afiliado
            Stat::make('Ganhos como Afiliado', Helper::amountFormatDecimal(Helper::formatNumber($totalAfiliados)))
                ->description('Total de Ganhos como afiliado')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Ganhos CPA', Helper::amountFormatDecimal(Helper::formatNumber($totalCpa)))
                ->description('Total de CPA recebido')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Ganhos RevShare', Helper::amountFormatDecimal(Helper::formatNumber($totalRevshare)))
                ->description('Total de RevShare recebido')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Saldo Afiliado', Helper::amountFormatDecimal(Helper::formatNumber($saldoAfiliado)))
                ->description('Saldo disponível para saque')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('warning'),
                
            Stat::make('Pessoas Indicadas', $totalIndicacoes)
                ->description('Total de usuários indicados')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make('Volume Indicados', Helper::amountFormatDecimal(Helper::formatNumber($totalDepositosIndicados)))
                ->description('Volume total depositado pelos indicados')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }

    /**
     * @return string
     */
    public static function canView(): bool
    {
        return true;
    }
}
