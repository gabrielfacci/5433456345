<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Widgets\StatsUserDetailOverview;
use App\Models\User;
use Filament\Resources\Pages\Page;

class DetailUser extends Page
{
    protected static ?string $title = 'Detalhes do Usuário';

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.detail-user';

    public \Illuminate\Database\Eloquent\Model|string|int|null $record;

    /**
     * @param string|int $record
     * @return void
     */
    public function mount(string|int $record): void
    {
        $this->record = User::findOrFail($record);
        
        // Debug para verificar se a página está sendo carregada
        \Log::info('DetailUser page loaded for record: ' . $record);
    }

    /**
     * @return int|string|array
     */
    public function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    /**
     * @return array
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    /**
     * @return string[]
     */
    public function getWidgets(): array
    {
        return [
            // Widget principal com estatísticas gerais
            StatsUserDetailOverview::class,
            
            // Widget de depósitos
            \App\Filament\Admin\Resources\UserResource\Widgets\DepositsOverview::class,
            
            // Widget de apostas
            \App\Filament\Admin\Resources\UserResource\Widgets\MyBetsTableWidget::class,
            
            // Widget de indicações e depósitos
            UserResource\Widgets\DepositsIndicationsOverview::class,
            
            // Widget de tabela de indicações
            UserResource\Widgets\IndicationsTableWidget::class,
        ];
    }

    /**
     * @return array
     */
    public function getFormActions(): array
    {
        return [];
    }

    /**
     * @return array|\Filament\Widgets\WidgetConfiguration[]|string[]
     */
    protected function getFooterWidgets(): array
    {
        return [];
    }

    /**
     * @return int|array
     */
    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'md' => 4,
            'xl' => 5,
        ];
    }

    /**
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('edit')
                ->label('Editar Usuário')
                ->icon('heroicon-o-pencil')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return "Detalhes: {$this->record->name}";
    }

    /**
     * @return array
     */
    public function getWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}
