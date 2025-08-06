<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GiftResource\Pages;
use App\Models\Gift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use App\Models\Game;

class GiftResource extends Resource
{
    protected static ?string $model = Gift::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
           Fieldset::make('Dados da Premiação')
    ->schema([
        TextInput::make('name')
            ->label('Nome do Presente')
            ->required(),

        Select::make('type')
            ->label('Tipo de Premiação')
            ->options([
                'money' => 'Valor em Dinheiro',
                'spins' => 'Rodadas Grátis',
            ])
            ->default('money')
            ->reactive()
            ->required(),

        TextInput::make('amount')
            ->label('Valor do Bônus (R$)')
            ->numeric()
            ->step('0.01')
            ->nullable()
            ->hidden(fn ($get) => $get('type') !== 'money'),

        TextInput::make('spins')
            ->label('Número de Rodadas Grátis')
            ->numeric()
            ->nullable()
            ->hidden(fn ($get) => $get('type') !== 'spins'),

        Select::make('game_code')
            ->label('Jogo das Rodadas Grátis')
            ->options(Game::pluck('game_name', 'game_code'))
            ->searchable()
            ->nullable()
            ->hidden(fn ($get) => $get('type') !== 'spins'),

        TextInput::make('code')
            ->label('Código de Resgate')
            ->required(),

        TextInput::make('quantity')
            ->label('Quantidade de codigos para resgate')
            ->numeric()
            ->required(),

        Toggle::make('is_active')
            ->label('Ativo')
            ->default(true),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nome'),
                Tables\Columns\TextColumn::make('amount')->label('Valor'),
                Tables\Columns\TextColumn::make('code')->label('Código'),
                Tables\Columns\TextColumn::make('spins')->label('Spins'),
                Tables\Columns\TextColumn::make('quantity')->label('Qtd. Resgates'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn ($record) => $record->is_active ? 'Desativar' : 'Ativar')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(function ($record) {
                        $record->is_active = !$record->is_active;
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGifts::route('/'),
            'create' => Pages\CreateGift::route('/create'),
            'edit' => Pages\EditGift::route('/{record}/edit'),
        ];
    }
}
