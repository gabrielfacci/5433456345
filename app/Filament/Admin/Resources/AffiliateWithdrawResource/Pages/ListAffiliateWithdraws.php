<?php

namespace App\Filament\Admin\Resources\AffiliateWithdrawResource\Pages;

use App\Filament\Admin\Resources\AffiliateWithdrawResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateWithdraws extends ListRecords
{
    protected static string $resource = AffiliateWithdrawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions::CreateAction::make(),
        ];
    }
} 