<?php

namespace App\Filament\Admin\Resources\VipRewardResource\Pages;

use App\Filament\Admin\Resources\VipRewardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVipReward extends EditRecord
{
    protected static string $resource = VipRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 