<?php

namespace App\Filament\Resources\MemberReports\Pages;

use App\Filament\Resources\MemberReports\MemberReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class ListMemberReports extends ListRecords
{
    protected static string $resource = MemberReportResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')

                ->action(function ($livewire) {

                    // ✅ Get filtered + current table data
                    $records = $livewire->getFilteredTableQuery()->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->title('No Data Found')
                            ->danger()
                            ->send();

                        return;
                    }

                    // ✅ Get ONLY visible columns (toggle columns)
                    $columns = collect($livewire->getTable()->getColumns())
                        ->filter(fn ($col) => $col->isVisible())
                        ->map(fn ($col) => [
                            'name' => $col->getName(),
                            'label' => $col->getLabel(),
                        ])
                        ->values();

                    // ✅ Generate PDF
                    $pdf = Pdf::loadView('pdf.members', [
                        'records' => $records,
                        'columns' => $columns,
                    ]);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'member_report.pdf'
                    );
                }),

        ];
    }
}