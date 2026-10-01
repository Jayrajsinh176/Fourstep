<?php

namespace App\Filament\Pages;

use App\Models\PayoutBatch;
use App\Models\PayoutDetail;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;

class PayoutHistoryDetails extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'payout-history-details/{batch}';

    protected string $view = 'filament.pages.payout-history-details';

    public PayoutBatch $batch;

    public function mount(PayoutBatch $batch): void
    {
        $this->batch = $batch;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PayoutDetail::query()
                    ->with(['member', 'kyc'])
                    ->where('batch_id', $this->batch->id)
            )

          ->columns([

    Tables\Columns\TextColumn::make('member.user_id')
        ->label('Member ID')
        ->searchable(),

    Tables\Columns\TextColumn::make('member.fullname')
        ->label('Member Name')
        ->searchable(),

    Tables\Columns\TextColumn::make('member.mobile_no')
        ->label('Mobile'),

    Tables\Columns\TextColumn::make('kyc.pan_number')
        ->label('PAN'),

    Tables\Columns\TextColumn::make('kyc.account_beneficiary_name')
        ->label('Account Holder'),

    Tables\Columns\TextColumn::make('kyc.account_no')
        ->label('Account No'),

    Tables\Columns\TextColumn::make('kyc.ifs_code')
        ->label('IFSC'),

    Tables\Columns\TextColumn::make('kyc.bank_name')
        ->label('Bank'),

    Tables\Columns\TextColumn::make('kyc.branch_name')
        ->label('Branch'),

    Tables\Columns\TextColumn::make('total_amount')
        ->label('Gross Amount')
        ->money('INR')
        ->weight('bold'),

    Tables\Columns\TextColumn::make('tds')
        ->label('TDS')
        ->money('INR')
        ->default('0.00'),

    Tables\Columns\TextColumn::make('admin_charge')
        ->label('Admin Charge')
        ->money('INR')
        ->default('0.00'),

    Tables\Columns\TextColumn::make('net_amount')
        ->label('Net Amount')
        ->money('INR')
        ->default('0.00'),

    Tables\Columns\TextColumn::make('reference_no')
        ->label('Reference No')
        ->default('-'),

    Tables\Columns\BadgeColumn::make('payment_status')
        ->label('Status')
        ->colors([
            'warning' => 'pending',
            'success' => 'paid',
        ]),
])

            ->defaultSort('id')

            ->searchable()

            ->defaultPaginationPageOption(10);
    }
    
protected function getHeaderActions(): array
{
    return [

        Action::make('markPaid')
            ->label('Mark as Paid')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->form([
                Forms\Components\TextInput::make('reference_no')
                    ->label('Reference No')
                    ->required(),

                Forms\Components\TextInput::make('transaction_no')
                    ->label('Transaction No'),

                Forms\Components\TextInput::make('cheque_no')
                    ->label('Cheque No'),
            ])
            ->action(function (array $data) {

                $details = PayoutDetail::where('batch_id', $this->batch->id)
                    ->where('payment_status', 'pending')
                    ->get();

                if ($details->isEmpty()) {

                    Notification::make()
                        ->title('No pending payout found.')
                        ->warning()
                        ->send();

                    return;
                }

                foreach ($details as $detail) {

                    $detail->update([
                        'reference_no' => $data['reference_no'],
                        'transaction_no' => $data['transaction_no'],
                        'cheque_no' => $data['cheque_no'],
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                $this->batch->update([
                    'status' => 'completed',
                ]);

                Notification::make()
                    ->title('Payout Marked as Paid')
                    ->body(
                        'All pending payout details in this batch have been marked as paid.'
                    )
                    ->success()
                    ->send();

                $this->resetTable();
            }),

        Action::make('pdf')
            ->label('Export PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->color('danger')
            ->url(fn () => route(
                'admin.payout-report.pdf',
                ['batch' => $this->batch->id]
            ))
            ->openUrlInNewTab(),

    ];
}

}