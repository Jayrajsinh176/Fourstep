<?php

namespace App\Filament\Resources\MemberOrder\Pages;

use App\Filament\Resources\MemberOrder\MemberOrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms;
use App\Models\Orderecom;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class ListMemberOrders extends ListRecords
{
    protected static string $resource = MemberOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('download')
                ->label('Download Member Orders')
                ->icon('heroicon-o-arrow-down-tray')

                // 📅 DATE FILTER
                ->form([
                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->required(),

                    Forms\Components\DatePicker::make('to_date')
                        ->label('To Date')
                        ->required(),
                ])

                ->action(function (array $data) {

                    // ✅ ONLY MEMBER ORDERS
$orders = Orderecom::with([
        'mlmMember',
        'items.product',
    ])
    ->whereNotNull('mlm_member_id')
    ->whereDate('created_at', '>=', $data['from_date'])
    ->whereDate('created_at', '<=', $data['to_date'])
    ->latest()
    ->get();

                    if ($orders->isEmpty()) {
                        Notification::make()
                            ->title('No Orders Found')
                            ->body('No member orders found for selected date.')
                            ->danger()
                            ->send();

                        return;
                    }

                   $pdf = Pdf::loadView('pdf.member-orders', [
                        'orders' => $orders,
                        'from' => $data['from_date'],
                        'to' => $data['to_date'],
                    ]);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'member_orders_' . $data['from_date'] . '_to_' . $data['to_date'] . '.pdf'
                    );
                }),

        ];
    }
}