<?php

namespace App\Filament\Resources\Orderecoms\Pages;

use App\Filament\Resources\Orderecoms\OrderecomResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Orderecom;
use Filament\Notifications\Notification;

class ListOrderecoms extends ListRecords
{
    protected static string $resource = OrderecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download Orders')
                ->icon('heroicon-o-arrow-down-tray')

                ->form([
                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->required(),

                    Forms\Components\DatePicker::make('to_date')
                        ->label('To Date')
                        ->required(),
                ])

                ->action(function (array $data) {

                   $orders = Orderecom::with([
    'ecomMember',
    'items.product',
])
                        ->whereDate('created_at', '>=', $data['from_date'])
                        ->whereDate('created_at', '<=', $data['to_date'])
                        ->latest()
                        ->get();

                    if ($orders->isEmpty()) {
                        Notification::make()
                            ->title('No Orders Found')
                            ->body('No orders available for selected date range.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $pdf = Pdf::loadView('pdf.orders', [
                        'orders' => $orders,
                        'from' => $data['from_date'],
                        'to' => $data['to_date'],
                    ]);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'orders_' . $data['from_date'] . '_to_' . $data['to_date'] . '.pdf'
                    );
                }),
        ];
    }
}