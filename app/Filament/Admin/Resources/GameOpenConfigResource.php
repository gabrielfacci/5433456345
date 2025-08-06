<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GameOpenConfigResource\Pages;
use App\Models\GameOpenConfig;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GameOpenConfigResource extends Resource
{
    protected static ?string $model = GameOpenConfig::class;

    protected static ?string $navigationIcon   = 'heroicon-o-lock-closed';
    protected static ?string $label            = 'Abertura de Jogos';
    protected static ?string $pluralLabel      = 'Abertura de Jogos';
    protected static ?string $navigationLabel  = 'Config. Abertura de Jogos';
    protected static ?string $navigationGroup  = 'QIC';

    // Só admin acessa
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    // Define o form
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configuração de Abertura de Jogos')
                ->description('Configure as regras para abertura de jogos pelos usuários.')
                ->schema([
                    Forms\Components\Toggle::make('requires_deposit_today')
                        ->label('Exigir Depósito Diário?')
                        ->helperText('Se ATIVO: usuário SEM saldo ou COM APENAS bônus precisa depositar hoje. Se tem saldo real, pode jogar sem depositar. Se DESATIVADO: pode jogar com qualquer saldo.')
                        ->default(false)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            \Log::info('GameOpenConfig atualizado', [
                                'requires_deposit_today' => $state,
                                'updated_by' => auth()->id(),
                                'timestamp' => now()
                            ]);
                        }),
                        
                    Forms\Components\Placeholder::make('info')
                        ->label('Informações Importantes')
                        ->content('
                            • Esta configuração afeta todos os usuários da plataforma
                            • 🔴 ATIVADO: apenas usuários SEM saldo ou COM APENAS bônus precisam depositar hoje
                            • 🔴 ATIVADO: usuários com saldo real (balance/balance_withdrawal) podem jogar SEM depositar
                            • 🟢 DESATIVADO: usuário pode jogar com qualquer saldo ou até sem saldo
                            • A verificação é feita tanto no backend quanto no frontend
                            • Logs são gerados para auditoria de tentativas de acesso
                        ')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // Tabela (geralmente não usada se for único registro, mas deixamos mínima)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('requires_deposit_today')
                    ->label('Exigir Depósito Diário')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Configurar'),
            ])
            ->bulkActions([])
            ->defaultPagination(1)
            ->description('Configure as regras para abertura de jogos pelos usuários.');
    }

    // Força a query a pegar só 1 registro
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }

    // Páginas
    public static function getPages(): array
    {
        return [
            'index' => Pages\EditGameOpenConfig::route('/'),
        ];
    }
}
