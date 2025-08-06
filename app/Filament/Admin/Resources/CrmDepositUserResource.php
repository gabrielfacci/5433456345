<?php

namespace App\Filament\Admin\Resources;

use App\Models\CrmDepositUser;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CrmDepositUserResource extends Resource
{
    protected static ?string $model = CrmDepositUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Clientes Depositantes';

    // Títulos personalizados
    protected static ?string $pluralModelLabel = 'CRM DEPOSITANTES';
    protected static ?string $modelLabel = 'CRM Depositante';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // leitura
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Telefone')->searchable(),
                Tables\Columns\TextColumn::make('deposits_count')->label('Qtd. Depósitos')->sortable(),
                Tables\Columns\TextColumn::make('deposits_total')->label('Total Depositado')->money('BRL', locale: 'pt_BR')->sortable(),
                Tables\Columns\TextColumn::make('last_deposit_at')->label('Último Depósito')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Filter::make('last_deposit_at')
                    ->label('Período do Último Depósito')
                    ->form([
                        DatePicker::make('from')->label('De'),
                        DatePicker::make('until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('last_deposit_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('last_deposit_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'De '.Carbon::parse($data['from'])->format('d/m/Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Até '.Carbon::parse($data['until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->url(fn($record) => 'https://wa.me/'.preg_replace('/\D/', '', (string) $record->phone))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\CrmDepositUserResource\Pages\ListCrmDepositUsers::route('/'),
        ];
    }
} 