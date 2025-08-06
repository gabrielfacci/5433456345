<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CashbackSettingResource\Pages;
use App\Models\CashbackSetting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashbackSettingResource extends Resource
{
    protected static ?string $model = CashbackSetting::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-uturn-left';
    protected static ?string $label           = 'Cashback';
    protected static ?string $pluralLabel     = 'Cashback';
    protected static ?string $navigationLabel = 'Config. Cashback';
    protected static ?string $navigationGroup = 'EXCLUSIVO QIC BUSINESS';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configurações de Cashback')
                ->description('Configure como o cashback será calculado e creditado aos usuários.')
                ->icon('heroicon-o-arrow-uturn-left')
                ->schema([
                    Forms\Components\TextInput::make('percentual')
                        ->label('Percentual de Cashback (%)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(10.00)
                        ->required()
                        ->helperText('Percentual das perdas que será devolvido como cashback'),

                    Forms\Components\Select::make('periodicidade')
                        ->label('Período de Cálculo')
                        ->options([
                            'daily'   => 'Diário (24 horas)',
                            'weekly'  => 'Semanal (7 dias)',
                            'monthly' => 'Mensal (30 dias)',
                        ])
                        ->default('weekly')
                        ->required()
                        ->helperText('Frequência com que o cashback será calculado'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Sistema Ativo')
                        ->default(true)
                        ->helperText('Ativar/desativar o sistema de cashback'),

                    Forms\Components\TextInput::make('min_cashback')
                        ->label('Cashback Mínimo (R$)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->default(1.00)
                        ->helperText('Valor mínimo para gerar cashback'),

                    Forms\Components\TextInput::make('max_cashback')
                        ->label('Cashback Máximo (R$)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->default(1000.00)
                        ->helperText('Valor máximo de cashback por período'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Informações do Sistema')
                ->description('Estatísticas e informações sobre o sistema de cashback.')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Forms\Components\Placeholder::make('info_table')
                        ->content(function () {
                            $config = CashbackSetting::first();
                            if (!$config) return 'Nenhuma configuração encontrada';
                            
                            $period = match($config->periodicidade) {
                                'daily' => 'Diário',
                                'weekly' => 'Semanal',
                                'monthly' => 'Mensal',
                                default => 'Indefinido'
                            };
                            
                            return view('filament.components.info-table', [
                                'items' => [
                                    'Percentual Atual' => $config->percentual . '%',
                                    'Período' => $period,
                                    'Status' => $config->is_active ?? true ? '✅ Ativo' : '❌ Inativo',
                                    'Mínimo' => 'R$ ' . number_format($config->min_cashback ?? 1, 2, ',', '.'),
                                    'Máximo' => 'R$ ' . number_format($config->max_cashback ?? 1000, 2, ',', '.'),
                                ]
                            ]);
                        })
                        ->columnSpanFull(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('percentual')
                    ->label('Percentual')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('periodicidade')
                    ->label('Período')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'daily' => 'Diário',
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensal',
                        default => $state
                    })
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo')
                    ->default(true),

                Tables\Columns\TextColumn::make('min_cashback')
                    ->label('Mínimo')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state ?? 1, 2, ',', '.'))
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('max_cashback')
                    ->label('Máximo')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state ?? 1000, 2, ',', '.'))
                    ->badge()
                    ->color('danger'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultPagination(1);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditCashbackSetting::route('/'),
        ];
    }
} 