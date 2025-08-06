<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use App\Models\Setting;

class SportsbookPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Sportsbook';
    protected static ?string $title = 'Configurações do Sportsbook';
    protected static ?string $navigationGroup = 'EXCLUSIVO QIC BUSINESS';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'sportsbook-config';
    protected static string $view = 'filament.admin.pages.sportsbook';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'sportsbook_enabled' => Setting::get('sportsbook_enabled', false),
            'sportsbook_url' => Setting::get('sportsbook_url', ''),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('sportsbook_enabled')
                    ->label('Ativar Sportsbook')
                    ->default(false),

                TextInput::make('sportsbook_url')
                    ->label('URL do Sportsbook')
                    ->placeholder('https://sportsbook.exemplo.com')
                    ->url()
                    ->required()
                    ->helperText('URL completa do Sportsbook que será exibida no iframe'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Configurações salvas com sucesso!')
            ->success()
            ->send();
    }
} 