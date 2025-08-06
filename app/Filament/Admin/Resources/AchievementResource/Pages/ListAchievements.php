<?php

namespace App\Filament\Admin\Resources\AchievementResource\Pages;

use App\Filament\Admin\Resources\AchievementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
} 