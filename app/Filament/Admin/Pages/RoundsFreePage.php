<?php

namespace App\Filament\Admin\Pages;

use App\Models\ConfigPlayFiver;
use App\Models\GamesKey;
use App\Models\Game;
use App\Services\PlayFiverService;
use App\Models\User;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoundsFreePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.rounds-free-page';

    protected static ?string $title = 'Rodadas Grátis';

    protected static ?string $slug = 'rounds-free';

    /**
     * @dev  
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin'); // Controla o acesso total à página
    }
    
    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin'); // Controla a visualização de elementos específicos
    }


    public ?array $data = [];
    public ?GamesKey $setting;

    /**
     * @return void
     */
    public function mount(): void
    {  
        $this->form->fill();
    }

/**
 * @param Form $form
 * @return Form
 */
public function form(Form $form): Form
{
    $users = User::pluck('email', 'email');
    $games = Game::pluck('game_name', 'game_code');

    return $form
        ->schema([
            Section::make('Rodadas Grátis')
                ->schema([
                            Select::make('email')
                            ->label('Player')
                            ->options($users)
                            ->searchable()
                            ->required(),
                            Select::make('game_code')
                            ->label('Jogo')
                            ->options($games)
                            ->searchable()
                            ->required(),
                            TextInput::make('rounds')
                                ->label('Quantidade de rodadas')
                                ->placeholder('Digite aqui a quantidade de rodadas')
                                ->numeric()
                                ->rules(['min:1', 'max:30'])
                                ->required()

                                ->maxLength(191),

                        ])->columns(3),
                      
            // Nova seção para solicitar a senha de 2FA antes de salvar alterações
            Section::make('Confirmação de Alteração')
                ->schema([
                    TextInput::make('admin_password')
                        ->label('Senha de 2FA')
                        ->placeholder('Digite a senha de 2FA')
                        ->password()
                        ->required()
                        // Esse método faz com que o valor não seja persistido no model
                        ->dehydrateStateUsing(fn($state) => null),
                ]),
        ])
        ->statePath('data');
}

    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            // Se a aplicação estiver em modo demo, bloqueia a alteração.
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title('Atenção')
                    ->body('Você não pode realizar esta alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }
    
            // Validação da senha de 2FA: Verifica se o campo 'admin_password' está presente
            // e se o valor informado bate com o token definido em TOKEN_DE_2FA.
            if (
                !isset($this->data['admin_password']) ||
                $this->data['admin_password'] !== env('TOKEN_DE_2FA')
            ) {
                Notification::make()
                    ->title('Acesso Negado')
                    ->body('A senha de 2FA está incorreta. Você não pode atualizar os dados.')
                    ->danger()
                    ->send();
                return;
            }
            
         
            $dados = [
                "username" => $this->data['email'],
                "game_code" => $this->data['game_code'],
                "rounds" => $this->data['rounds']
            ];
            $roundsFree = PlayFiverService::RoundsFree($dados);
            if($roundsFree['status']){
                Notification::make()
                ->title('Rodadas grátis')
                ->body('As rodadas grátis foram agendadas.')
                ->success()
                ->send();
            }else{
                Notification::make()
                ->title('Rodadas grátis')
                ->body($roundsFree['message'])
                ->danger()
                ->send();
            }

        } catch (Halt $exception) {
            Notification::make()
                ->title('Erro ao alterar dados!')
                ->body('Erro ao alterar dados!')
                ->danger()
                ->send();
        }
    }
    
}
