<?php

namespace App\Filament\Admin\Resources\CrmSignupResource\Pages;

use App\Filament\Admin\Resources\CrmSignupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class ListCrmSignups extends ListRecords
{
    protected static string $resource = CrmSignupResource::class;

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

        $html = view('pdf.crm-signups', [
            'records' => $records,
        ])->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'crm-cadastros.pdf');
    }
} 