<?php

namespace App\Filament\Admin\Resources\RaspadinhaResource\Pages;

use App\Filament\Admin\Resources\RaspadinhaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRaspadinha extends EditRecord
{
    protected static string $resource = RaspadinhaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 