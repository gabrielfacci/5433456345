<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DailyBonusConfigResource\Pages;
use App\Models\DailyBonusConfig;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyBonusConfigResource extends Resource
{
    protected static ?string $model = DailyBonusConfig::class;

    protected static ?string $navigationIcon   = 'heroicon-o-ticket';
    protected static ?string $label            = 'Bônus Diário';
    protected static ?string $pluralLabel      = 'Bônus Diário'; // ou "Bônus Diários"
    protected static ?string $navigationLabel  = 'Config. Bônus Diário';
    protected static ?string $navigationGroup  = 'Finanças';

    /**
     * Restringe acesso a admins, por exemplo.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Formulário (campos editáveis) de edição/criação do registro.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configuração do Bônus Diário')
                ->description('Configure o sistema de bônus diário para os usuários.')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativar Bônus Diário?')
                        ->helperText('Se ativo, usuários poderão resgatar bônus diário.')
                        ->default(true)
                        ->live(),

                    Forms\Components\TextInput::make('bonus_value')
                        ->label('Valor do Bônus (R$)')
                        ->numeric()
                        ->step(0.01)
                        ->default(10.00)
                        ->minValue(0.01)
                        ->maxValue(1000)
                        ->required()
                        ->suffix('R$'),

                    Forms\Components\TextInput::make('cycle_hours')
                        ->label('Intervalo entre Resgates (Horas)')
                        ->numeric()
                        ->default(24)
                        ->minValue(1)
                        ->maxValue(168)
                        ->required()
                        ->helperText('Ex: 24 horas = 1 vez por dia, 12 horas = 2 vezes por dia'),

                    Forms\Components\Select::make('bonus_type')
                        ->label('Tipo de Bônus')
                        ->options([
                            'balance_bonus' => 'Saldo Bônus (com rollover)',
                            'balance_withdrawal' => 'Saldo de Saque (sem rollover)'
                        ])
                        ->default('balance_bonus')
                        ->required()
                        ->helperText('Onde o bônus será creditado na carteira do usuário'),

                    Forms\Components\Placeholder::make('info')
                        ->label('Informações Importantes')
                        ->content('
                            • O bônus será creditado automaticamente na carteira do usuário
                            • O intervalo é calculado desde o último resgate do usuário
                            • Logs são gerados para auditoria dos resgates
                            • Usuários podem resgatar apenas se o intervalo tiver passado
                        ')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    /**
     * Tabela de colunas, se fosse exibir, mas neste caso vamos usar apenas "Edit" direto.
     *
     * Vamos manter algo mínimo, e redirecionar para a edição.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('bonus_value')
                    ->label('Valor do Bônus')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cycle_hours')
                    ->label('Intervalo')
                    ->suffix(' horas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bonus_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'balance_bonus' => 'warning',
                        'balance_withdrawal' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Configurar'),
            ])
            ->bulkActions([])
            ->defaultPagination(1)
            ->description('Configure o sistema de bônus diário para os usuários.');
    }

    /**
     * Força a query para pegar somente 1 registro.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }

    /**
     * Define as rotas/páginas do Filament.
     * Em vez de create/index/edit separados, faremos como no Mines: "index" => EditDailyBonusConfig
     */
    public static function getPages(): array
    {
        return [
            // Ao acessar /daily-bonus-configs, irá diretamente para a página de editar
            'index' => Pages\EditDailyBonusConfig::route('/'),
        ];
    }
}
