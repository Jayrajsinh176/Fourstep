<?php

namespace App\Filament\Resources\PurchaseBalanceRequests\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Services\ActivityLogService;

class PurchaseBalanceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('member_id')
                    ->label('Member')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('mode_of_payment')
                    ->label('Payment Mode'),

                Tables\Columns\TextColumn::make('transaction_no')
                    ->label('Txn No'),

                // ✅ PAYMENT SLIP
                Tables\Columns\TextColumn::make('payment_slip')
                    ->label('Slip')
                    ->formatStateUsing(fn ($state) => $state ? 'View' : '-')
                    ->url(fn ($record) => $record->payment_slip 
                        ? asset('storage/' . $record->payment_slip) 
                        : null
                    )
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])

            // ✅ FILTER
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])

            // ✅ ACTION BUTTONS (CORRECT WAY)
            ->actions([

                // ✅ APPROVE
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                   ->action(function ($record) {

    $record->update([
        'status' => 'approved',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'Purchase Balance Approved',
        'Approved purchase balance request of member ' .
        $record->member_id .
        ' | Amount ₹' . number_format($record->amount, 2)
    );
}),

                // ❌ REJECT
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                   ->action(function ($record) {

    $record->update([
        'status' => 'rejected',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'Purchase Balance Rejected',
        'Rejected purchase balance request of member ' .
        $record->member_id .
        ' | Amount ₹' . number_format($record->amount, 2)
    );
}),
            ])

            ->bulkActions([]);
    }
}