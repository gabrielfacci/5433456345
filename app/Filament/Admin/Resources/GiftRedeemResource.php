<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GiftRedeemResource\Pages;
use App\Filament\Admin\Resources\GiftRedeemResource\RelationManagers;
use App\Models\GiftRedeem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GiftRedeemResource extends Resource
{
    protected static ?string $model = GiftRedeem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Não precisa de formulário, apenas visualização
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('gift.name')->label('Premiação'),
                Tables\Columns\TextColumn::make('user.name')->label('Usuário'),
                Tables\Columns\TextColumn::make('user.email')->label('E-mail'),
                Tables\Columns\TextColumn::make('amount')->label('Valor'),
                Tables\Columns\TextColumn::make('spins')->label('Spins'),
                Tables\Columns\TextColumn::make('code')->label('Código'),
                Tables\Columns\IconColumn::make('is_used')->label('Usado')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Resgatado em')->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                // Exemplo: filtro por usado/não usado
                Tables\Filters\TernaryFilter::make('is_used')->label('Usado'),
            ])
            ->actions([
                // Apenas visualização, sem editar/excluir
            ])
            ->bulkActions([
                // Nenhuma ação em massa
            ]);
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
            'index' => Pages\ListGiftRedeems::route('/'),
            'create' => Pages\CreateGiftRedeem::route('/create'),
            'edit' => Pages\EditGiftRedeem::route('/{record}/edit'),
        ];
    }
}
