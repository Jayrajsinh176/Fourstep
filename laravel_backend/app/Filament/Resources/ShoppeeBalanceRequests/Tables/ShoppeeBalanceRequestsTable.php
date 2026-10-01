<?php

namespace App\Filament\Resources\ShoppeeBalanceRequests\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use App\Models\Shoppee_Transaction;
use Filament\Forms;
use App\Services\ActivityLogService;

class ShoppeeBalanceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                // ✅ FIXED MEMBER NAME (SAFE)
                Tables\Columns\TextColumn::make('member.fullname')
                    ->label('Member')
                    ->formatStateUsing(function ($state, $record) {
                        return ($state ?? 'N/A') . ' (' . ($record->member->member_id ?? '-') . ')';
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('INR')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('mode_of_payment'),

                Tables\Columns\TextColumn::make('transaction_no'),

                // ✅ IMAGE FIX FOR CPANEL (PUBLIC FOLDER)
                Tables\Columns\ImageColumn::make('payment_slip')
    ->label('Payment Slip')
    ->getStateUsing(function ($record) {
        return $record->payment_slip
            ? asset($record->payment_slip)
            : null;
    })
    ->height(70)
    ->width(70)
    ->square()
    ->url(fn ($record) =>
        $record->payment_slip
            ? asset($record->payment_slip)
            : null
    )
    ->openUrlInNewTab(),
 Tables\Columns\BadgeColumn::make('status')
    ->colors([
        'warning' => 'pending',
        'success' => 'Approved',
        'danger' => 'Rejected',
    ]),

Tables\Columns\TextColumn::make('reject_reason')
    ->label('Reject Reason')
    ->placeholder('-')
    ->wrap()
    ->extraAttributes([
        'style' => 'max-width:250px; word-break: break-word;',
    ]),
            ])

            ->recordUrl(null)

            // ✅ FILTER
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
                     Tables\Filters\SelectFilter::make('type')
        ->label('Type')
        ->options([
            'purchase' => 'Purchase',
            'turnover' => 'Turnover',
        ]),
            ])

            // ✅ ACTIONS
->recordActions([

    // ✅ APPROVE
    Action::make('approve')
        ->label('Approve')
        ->icon('heroicon-o-check')
        ->color('success')
        ->visible(fn ($record) => $record->status === 'pending')
        ->requiresConfirmation()
        ->action(function ($record) {

            if ($record->status !== 'pending') {
                return;
            }

            $record->update(['status' => 'Approved']);

            $exists = Shoppee_Transaction::where('ref_id', $record->id)
                ->where('ref_type', 'balance_request')
                ->exists();

            if (!$exists) {

                Shoppee_Transaction::create([
                    'member_id' => $record->member_id,
                    'ref_id' => $record->id,
                    'ref_type' => 'balance_request',
                    'balance_type' => $record->type,
                    'entry_type' => 'credit',
                    'amount' => $record->amount,
                    'detail' => 'Credited Against ' . ucfirst($record->type) . ' Request#' . $record->id,
                ]);
                
                ActivityLogService::log(
    'Shoppee Panel',
    'Shoppee Balance Approved',
    'Approved ' . ucfirst($record->type) .
    ' balance request for ' .
    $record->member->fullname .
    ' (' . $record->member->member_id . ')' .
    ' | Amount ₹' . number_format($record->amount, 2)
);
            }
        }),

Action::make('reject')
    ->label('Reject')
    ->icon('heroicon-o-x-mark')
    ->color('danger')
    ->visible(fn ($record) => $record->status === 'pending')
    ->form([
        Forms\Components\Textarea::make('reject_reason')
            ->label('Rejection Reason')
            ->required()
            ->rows(4)
            ->placeholder('Enter rejection reason'),
    ])
    ->modalHeading('Reject Balance Request')
    ->modalSubmitActionLabel('Reject Request')
    ->action(function ($record, array $data) {

        $record->update([
            'status' => 'Rejected',
            'reject_reason' => $data['reject_reason'],
        ]);
        
        ActivityLogService::log(
    'Shoppee Panel',
    'Shoppee Balance Rejected',
    'Rejected ' . ucfirst($record->type) .
    ' balance request for ' .
    $record->member->fullname .
    ' (' . $record->member->member_id . ')' .
    ' | Amount ₹' . number_format($record->amount, 2) .
    ' | Reason: ' . $data['reject_reason']
);
    }),

])//

            // ✅ BULK DELETE
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(),
            ]);
    }
}