<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Components\{FileUpload, TextInput, Textarea, Grid, Select};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\{ImageColumn, TextColumn, BadgeColumn};
use Filament\Tables\Table;
use Filament\Tables\Actions\{EditAction, DeleteAction, DeleteBulkAction};

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $label = 'Conquista';
    protected static ?string $navigationLabel = 'Conquistas';
    protected static ?string $pluralLabel = 'Conquistas';
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
                    ->label('Título da Conquista')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpan(2),

                TextInput::make('icon')
                    ->label('Ícone (Emoji)')
                    ->placeholder('🏆')
                    ->maxLength(10),

                FileUpload::make('image')
                    ->label('Imagem da Conquista')
                    ->image()
                    ->directory('achievements')
                    ->columnSpan(2),

                TextInput::make('vip_points_reward')
                    ->label('VIP Points ao desbloquear')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Select::make('requirement_type')
                    ->label('Tipo de Requisito')
                    ->options([
                        'apostas' => 'Apostas',
                        'missoes' => 'Missões',
                        'depositos' => 'Depósitos',
                        'vitorias' => 'Vitórias',
                        'outro' => 'Outro',
                    ])
                    ->required(),

                TextInput::make('requirement_value')
                    ->label('Valor do Requisito')
                    ->numeric()
                    ->required()
                    ->helperText(fn ($get) => match ($get('requirement_type')) {
                        'depositos' => 'Valor total em R$ que o usuário precisa depositar (ex: 50 = R$ 50,00)',
                        'apostas' => 'Número de apostas que o usuário precisa fazer',
                        'missoes' => 'Número de missões que o usuário precisa completar',
                        'vitorias' => 'Número de vitórias que o usuário precisa ter',
                        default => 'Valor necessário para desbloquear a conquista',
                    }),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                    ])
                    ->default('active')
                    ->required(),

                TextInput::make('total_limit')
                    ->label('Limite Total de Desbloqueios por Usuário')
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
                    ->defaultImageUrl(fn ($record) => 'data:image/svg+xml;base64,' . base64_encode('<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg"><text x="20" y="25" font-size="20" text-anchor="middle" fill="#666">' . ($record->icon ?: '🏆') . '</text></svg>')),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vip_points_reward')
                    ->label('VIP Points')
                    ->sortable(),

                BadgeColumn::make('requirement_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'apostas' => 'Apostas',
                        'missoes' => 'Missões',
                        'depositos' => 'Depósitos',
                        'vitorias' => 'Vitórias',
                        'outro' => 'Outro',
                        default => $state,
                    }),

                TextColumn::make('requirement_value')
                    ->label('Valor Req.')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->requirement_type === 'depositos') {
                            return 'R$ ' . number_format($state, 2, ',', '.');
                        }
                        return $state;
                    })
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Ativa' : 'Inativa'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
} 