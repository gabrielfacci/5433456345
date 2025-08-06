<?php

namespace App\Filament\Admin\Resources\VipRewardResource\Pages;

use App\Filament\Admin\Resources\VipRewardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVipRewards extends ListRecords
{
    protected static string $resource = VipRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
} 