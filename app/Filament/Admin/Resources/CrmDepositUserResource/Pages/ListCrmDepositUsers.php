<?php

namespace App\Filament\Admin\Resources\CrmDepositUserResource\Pages;

use App\Filament\Admin\Resources\CrmDepositUserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class ListCrmDepositUsers extends ListRecords
{
    protected static string $resource = CrmDepositUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportPdf')
                ->requiresConfirmation()
                ->color('success'),
        ];
    }

    public function exportPdf()
    {
        $records = $this->getTableQuery()->get();

        $html = view('pdf.crm-deposit-users', [
            'records' => $records,
        ])->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'crm-depositantes.pdf');
    }
} 