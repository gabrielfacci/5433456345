<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TwoFactorAuthPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $title = 'Verificação de Dois Fatores';
    protected static ?string $slug = '2fa-verify';
    protected static ?string $navigationLabel = '2FA';
    protected static ?int $navigationSort = 1;

    // Esconde do menu de navegação
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.two-factor-auth';

    public ?array $data = [];

    public function mount(): void
    {
        // Verifica se o usuário está autenticado e é admin
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            redirect()->to('/stv/login');
            return;
        }

        // Se já passou pela 2FA, redireciona para o dashboard
        if (Session::get('2fa_verified')) {
            redirect()->to('/stv');
            return;
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('password')
                    ->label('Senha de Segurança')
                    ->password()
                    ->required()
                    ->placeholder('Digite a senha de segurança')
                    ->autofocus()
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        $data = $this->form->getState();
        
        $password = $data['password'] ?? '';
        $expectedPassword = env('TOKEN_ADMIN_DE_2FA', 'admin123');

        if (empty($expectedPassword)) {
            Notification::make()
                ->title('Erro de Configuração')
                ->body('Token de 2FA administrativo não configurado no ambiente.')
                ->danger()
                ->send();

            Log::error('2FA Admin Token não configurado no ambiente');
            return;
        }

        if ($password === $expectedPassword) {
            // Marca como verificado na sessão
            Session::put('2fa_verified', true);
            
            // Log de sucesso
            Log::info('2FA Admin verificado com sucesso', [
                'user' => auth()->user()->email,
                'ip' => request()->ip()
            ]);

            Notification::make()
                ->title('Verificação Concluída')
                ->body('Acesso administrativo liberado com sucesso!')
                ->success()
                ->send();

            // Redireciona para o dashboard
            redirect()->to('/stv');
        } else {
            // Log de tentativa falhada
            Log::warning('Tentativa de 2FA Admin falhada', [
                'user' => auth()->user()->email,
                'ip' => request()->ip()
            ]);

            Notification::make()
                ->title('Senha Incorreta')
                ->body('A senha de segurança administrativa está incorreta. Tente novamente.')
                ->danger()
                ->send();

            // Limpa o campo de senha
            $this->form->fill(['password' => '']);
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('verify')
                ->label('Verificar Acesso')
                ->submit('verify')
                ->color('primary')
                ->size('lg')
                ->icon('heroicon-o-check-circle'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('logout')
                ->label('Sair')
                ->url('/stv/login')
                ->color('danger')
                ->icon('heroicon-o-arrow-left-on-rectangle'),
        ];
    }
} 