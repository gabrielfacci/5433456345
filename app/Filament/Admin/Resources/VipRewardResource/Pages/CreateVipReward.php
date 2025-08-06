<?php

namespace App\Filament\Admin\Resources\VipRewardResource\Pages;

use App\Filament\Admin\Resources\VipRewardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVipReward extends CreateRecord
{
    protected static string $resource = VipRewardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 