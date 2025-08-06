<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RaspadinhaResource\Pages;
use App\Models\Raspadinha;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RaspadinhaResource extends Resource
{
    protected static ?string $model = Raspadinha::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'EXCLUSIVO QIC BUSINESS';

    protected static ?string $navigationLabel = 'Raspadinhas';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configurações da Raspadinha')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome da Raspadinha')
                            ->required()
                            ->placeholder('Ex: Raspadinha Básica')
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem da Raspadinha')
                            ->image()
                            ->directory('scratch_cards')
                            ->visibility('public')
                            ->required()
                            ->placeholder('Carregue uma imagem PNG/JPG'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->placeholder('Ex: Ganhe até R$ 2.000,00')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\TextInput::make('price')
                            ->label('Preço (Frontend)')
                            ->required()
                            ->placeholder('Ex: R$ 1,00')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('max_prize')
                            ->label('Prêmio Máximo')
                            ->required()
                            ->placeholder('Ex: R$ 2.000')
                            ->maxLength(50),

                        Forms\Components\Select::make('category')
                            ->label('Categoria')
                            ->required()
                            ->options([
                                'dinheiro' => 'Dinheiro',
                                'misto' => 'Misto',
                                'produtos' => 'Produtos'
                            ])
                            ->default('dinheiro'),

                        Forms\Components\TextInput::make('win_chance_percentage')
                            ->label('Chance de Ganho (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(5)
                            ->helperText('Porcentagem exibida no modal do frontend (apenas visual)')
                            ->suffix('%'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativa')
                            ->default(true)
                            ->helperText('Se desativada, a raspadinha não aparecerá no site'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordem de Exibição')
                            ->numeric()
                            ->default(0)
                            ->helperText('Número menor = aparece primeiro'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagem')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dinheiro' => 'success',
                        'misto' => 'warning',
                        'produtos' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('win_chance_percentage')
                    ->label('Chance (%)')
                    ->sortable()
                    ->suffix('%'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'misto' => 'Misto',
                        'produtos' => 'Produtos'
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->trueLabel('Ativas')
                    ->falseLabel('Inativas')
                    ->native(false),
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
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListRaspadinhas::route('/'),
            'create' => Pages\CreateRaspadinha::route('/create'),
            'edit' => Pages\EditRaspadinha::route('/{record}/edit'),
        ];
    }
} 