<?php

namespace App\Filament\Admin\Resources;

use App\Models\SystemNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemNotificationResource extends Resource
{
    protected static ?string $model = SystemNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'QIC Business';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->required(),
                        Forms\Components\TextInput::make('link')
                            ->label('Link (opcional)')
                            ->url()
                            ->maxLength(191),
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem')
                            ->directory('imagem')
                            ->disk('public')
                            ->visibility('public')
                            ->image(),
                        Forms\Components\Toggle::make('active')
                            ->label('Ativa')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Imagem'),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('active')->boolean()->label('Ativa'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Criada em')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\SystemNotificationResource\Pages\ListSystemNotifications::route('/'),
            'create' => \App\Filament\Admin\Resources\SystemNotificationResource\Pages\CreateSystemNotification::route('/create'),
            'edit' => \App\Filament\Admin\Resources\SystemNotificationResource\Pages\EditSystemNotification::route('/{record}/edit'),
        ];
    }
} 