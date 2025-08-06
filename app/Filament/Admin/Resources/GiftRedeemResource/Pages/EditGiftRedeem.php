<?php

namespace App\Filament\Admin\Resources\GiftRedeemResource\Pages;

use App\Filament\Admin\Resources\GiftRedeemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGiftRedeem extends EditRecord
{
    protected static string $resource = GiftRedeemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
