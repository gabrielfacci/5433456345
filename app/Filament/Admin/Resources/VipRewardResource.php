<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VipRewardResource\Pages;
use App\Models\VipReward;
use Filament\Forms;
use Filament\Forms\Components\{FileUpload, Select, TextInput, Textarea, Grid};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\{ImageColumn, TextColumn, BadgeColumn, IconColumn};
use Filament\Tables\Table;

class VipRewardResource extends Resource
{
    protected static ?string $model = VipReward::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $label = 'Recompensa VIP';
    protected static ?string $navigationLabel = 'Recompensas VIP';
    protected static ?string $pluralLabel = 'Recompensas VIP';
    protected static ?string $navigationGroup = 'EXCLUSIVO QIC BUSINESS';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('title')
                    ->label('Título da Recompensa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpan(2),

                Select::make('type')
                    ->label('Tipo de Recompensa')
                    ->options([
                        'money' => 'Dinheiro (R$) - Vai para carteira de jogos',
                        'spins' => 'Giros Grátis',
                        'cashback' => 'Cashback - Vai para carteira de jogos',
                        'bonus' => 'Bônus - Vai para carteira de bônus',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state !== 'spins') {
                            $set('spins_quantity', null);
                            $set('game_id', null);
                        }
                    }),

                TextInput::make('value')
                    ->label('Valor (R$)')
                    ->numeric()
                    ->step(0.01)
                    ->visible(fn ($get) => in_array($get('type'), ['money', 'cashback', 'bonus']))
                    ->required(fn ($get) => in_array($get('type'), ['money', 'cashback', 'bonus'])),

                TextInput::make('spins_quantity')
                    ->label('Quantidade de Giros')
                    ->numeric()
                    ->visible(fn ($get) => $get('type') === 'spins')
                    ->required(fn ($get) => $get('type') === 'spins'),

                Select::make('game_id')
                    ->label('Jogo (para Giros Grátis)')
                    ->options(
                        \DB::table('games')->pluck('game_name', 'game_id')->toArray()
                    )
                    ->searchable()
                    ->visible(fn ($get) => $get('type') === 'spins')
                    ->required(fn ($get) => $get('type') === 'spins'),

                TextInput::make('vip_level_required')
                    ->label('Nível VIP Necessário')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->minValue(1)
                    ->maxValue(20),

                TextInput::make('points_cost')
                    ->label('Custo em Pontos VIP')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required(),

                TextInput::make('icon')
                    ->label('Ícone (Emoji)')
                    ->placeholder('💰')
                    ->maxLength(10),

                FileUpload::make('image')
                    ->label('Imagem da Recompensa')
                    ->image()
                    ->directory('vip-rewards')
                    ->columnSpan(2),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                    ])
                    ->default('active')
                    ->required(),

                TextInput::make('daily_limit')
                    ->label('Limite Diário de Resgates')
                    ->numeric()
                    ->nullable()
                    ->helperText('Deixe vazio para sem limite'),

                TextInput::make('total_limit')
                    ->label('Limite Total de Resgates por Usuário')
                    ->numeric()
                    ->nullable()
                    ->helperText('Deixe vazio para sem limite'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'data:image/svg+xml;base64,' . base64_encode('<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg"><text x="20" y="25" font-size="20" text-anchor="middle" fill="#666">' . ($record->icon ?: '🎁') . '</text></svg>')),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'money' => 'Dinheiro',
                        'spins' => 'Giros Grátis',
                        'cashback' => 'Cashback',
                        'bonus' => 'Bônus',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'money',
                        'info' => 'spins',
                        'warning' => 'cashback',
                        'primary' => 'bonus',
                    ]),

                TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(function ($record) {
                        return match ($record->type) {
                            'spins' => $record->spins_quantity . ' giros',
                            default => 'R$ ' . number_format($record->value, 2, ',', '.'),
                        };
                    })
                    ->sortable(),

                TextColumn::make('vip_level_required')
                    ->label('Nível VIP')
                    ->sortable(),

                TextColumn::make('points_cost')
                    ->label('Custo em Pontos')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'money' => 'Dinheiro',
                        'spins' => 'Giros Grátis',
                        'cashback' => 'Cashback',
                        'bonus' => 'Bônus',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVipRewards::route('/'),
            'create' => Pages\CreateVipReward::route('/create'),
            'edit' => Pages\EditVipReward::route('/{record}/edit'),
        ];
    }
} 