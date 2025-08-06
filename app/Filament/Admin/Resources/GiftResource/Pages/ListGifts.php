<?php

namespace App\Filament\Admin\Resources\GiftResource\Pages;

use App\Filament\Admin\Resources\GiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGifts extends ListRecords
{
    protected static string $resource = GiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Criar premiação'),
        ];
    }    
}
