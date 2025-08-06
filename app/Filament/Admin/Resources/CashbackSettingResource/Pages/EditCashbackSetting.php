<?php

namespace App\Filament\Admin\Resources\CashbackSettingResource\Pages;

use App\Filament\Admin\Resources\CashbackSettingResource;
use App\Models\CashbackSetting;
use Filament\Resources\Pages\EditRecord;

class EditCashbackSetting extends EditRecord
{
    protected static string $resource = CashbackSettingResource::class;

    /**
     * Garante que sempre haverá um registro a ser editado
     */
    public function mount($record = null): void
    {
        $found = CashbackSetting::singleton();
        $record = $found->id;
        parent::mount($record);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
} 