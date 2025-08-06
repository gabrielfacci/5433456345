<?php

namespace App\Filament\Admin\Resources\GiftRedeemResource\Pages;

use App\Filament\Admin\Resources\GiftRedeemResource;
use Filament\Resources\Pages\ListRecords;

class ListGiftRedeems extends ListRecords
{
    protected static string $resource = GiftRedeemResource::class;

    public function getTitle(): string
    {
        return 'Resgates de Premiação';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
