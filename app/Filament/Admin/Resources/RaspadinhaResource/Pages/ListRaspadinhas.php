<?php

namespace App\Filament\Admin\Resources\RaspadinhaResource\Pages;

use App\Filament\Admin\Resources\RaspadinhaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRaspadinhas extends ListRecords
{
    protected static string $resource = RaspadinhaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 