<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\StatsOverview;
use App\Livewire\AdminWidgets;
use App\Livewire\WalletOverview;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\HtmlString;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use App\Filament\Admin\Widgets\ReportsTableWidget;
use Filament\Forms\Components\Section;

class DashboardAdmin extends \Filament\Pages\Dashboard
{
    use HasFiltersForm, HasFiltersAction;

    /**
     * @dev @anonymous
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }


    /**
     * @return string|\Illuminate\Contracts\Support\Htmlable|null
     */
    public function getSubheading(): string| null|\Illuminate\Contracts\Support\Htmlable
    {
        return "Bem-vindo(a), Admin! Seu painel está pronto para você.";
    }
    

    /**
     * @param Form $form
     * @return Form
     */
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('SCRIPT DISPONIBILIZADO POR  @qicbusiness, SE VOCÊ ADQUIRIU EM OUTRO LUGAR TOMOU GOLPE')
                ->description(new HtmlString('
                    <div style="font-weight: 600; display: flex; align-items: center;">
                        adquira scripts pagos e gratuitos, acesse agora! 
                        <a class="dark:text-white" 
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #d28a00;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           " 
                           href="https://www.youtube.com/@queminvestecresce" 
                           target="_blank">
                            YOUTUBE
                        </a>
                         <a class="dark:text-white" 
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #d28a00;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           " 
                           href="https://www.instagram.com/stiveronald__" 
                           target="_blank">
                              INSTAGRAM
                        </a>
                        <a class="dark:text-white" 
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #d28a00;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           " 
                           href="https://t.me/qicbusiness" 
                           target="_blank">
                              TELEGRAM
                        </a>
                    </div>
            ')),
            ]);
    }

    /**
     * @return array|\Filament\Actions\Action[]|\Filament\Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Filtro')
                ->form([
                    DatePicker::make('startDate')->label('Data Incial'),
                    DatePicker::make('endDate')->label('Data Final'),
                ]),
        ];
    }


    /**
     * @return string[]
     */
    public function getWidgets(): array
    {
        return [
           
            StatsOverview::class,
            WalletOverview::class,
            AdminWidgets::class,
            ReportsTableWidget::class,
           
            //GGROverview::class,
            //GgrTableWidget::class,
        ];
    }
}
