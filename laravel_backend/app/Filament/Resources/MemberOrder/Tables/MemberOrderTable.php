<?php

namespace App\Filament\Resources\MemberOrder\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable(),

                TextColumn::make('invoice_id')
                    ->label('Invoice')
                    ->searchable()
                    ->default('-'),
                    
TextColumn::make('member_name')
    ->label('Member Name')
    ->getStateUsing(fn ($record) =>
        $record->mlmMember?->fullname ?? '-'
    ),

                TextColumn::make('mlm_member_id')
                    ->label('MLM ID')
                    ->badge()
                    ->color('success'),

           TextColumn::make('mobile')
    ->label('Mobile')
    ->getStateUsing(fn ($record) =>
        $record->mlmMember?->mobile_no ?? '-'
    ),

              TextColumn::make('address')
    ->label('Member Address')
    ->getStateUsing(fn ($record) =>
        $record->mlmMember?->address ?? '-'
    ),

                // ✅ DELIVERY NAME + ID
                TextColumn::make('delivery_name')
                    ->label('Delivery')
                    ->formatStateUsing(function ($record) {
                        if ($record->delivery_member_id) {
                            return ($record->delivery_name ?? '-') . ' (' . $record->delivery_member_id . ')';
                        }
                        return $record->delivery_name ?? '-';
                    })
                    ->wrap(),

                TextColumn::make('delivery_address')
                    ->label('Delivery Address')
                    ->wrap()
                    ->limit(25)
                    ->default('-'),

                 TextColumn::make('items.product.name')
    ->label('Product')
    ->badge()
    ->getStateUsing(function ($record) {
        return $record->items
            ->pluck('product.name')
            ->filter()
            ->implode(', ');
    }),


                TextColumn::make('quantity')
                    ->label('Qty'),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge(),

                // ✅ WALLET
                TextColumn::make('wallet_used')
                    ->label('Wallet')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Used' : 'No')
                    ->colors([
                        'success' => fn ($state) => $state == 1,
                        'gray' => fn ($state) => $state == 0,
                    ]),

                TextColumn::make('wallet_type')
                    ->label('Wallet Type')
                    ->default('-'),

                TextColumn::make('wallet_amount')
                    ->label('Wallet Amt')
                    ->money('INR'),

                TextColumn::make('coupon_code')
                    ->label('Coupon')
                    ->default('-'),

                TextColumn::make('coupon_discount')
                    ->label('Discount')
                    ->money('INR'),

                TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('INR'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'info' => 'dispatched',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ]),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),

            ])

            ->filters([])

            ->recordActions([

                // 👁 VIEW INVOICE (NEW)
                Action::make('view_invoice')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->modalHeading('Member Invoice')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('invoice.member-invoice', [
                        'order' => $record
                    ])),

                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}