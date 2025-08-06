<?php

namespace App\Filament\Admin\Resources\CupomResource\Pages;

use App\Filament\Admin\Resources\CupomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCupoms extends ListRecords
{
    protected static string $resource = CupomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
