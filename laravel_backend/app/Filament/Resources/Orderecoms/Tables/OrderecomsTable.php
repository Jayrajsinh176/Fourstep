<?php

namespace App\Filament\Resources\Orderecoms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class OrderecomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

->columns([

    TextColumn::make('id')
        ->label('Order ID')
        ->sortable(),

    // ✅ CUSTOMER
    TextColumn::make('delivery_name')
        ->label('Customer')
        ->getStateUsing(fn ($record) => $record->delivery_name ?? 'N/A')
        ->searchable()
        ->sortable(),

    // ✅ ADDRESS
    TextColumn::make('delivery_address')
        ->label('Address')
        ->wrap()
        ->getStateUsing(fn ($record) => $record->delivery_address ?? 'N/A'),

   TextColumn::make('items.product.name')
    ->label('Product')
    ->badge()
    ->getStateUsing(function ($record) {
        return $record->items
            ->pluck('product.name')
            ->filter()
            ->implode(', ');
    }),

    // ✅ COUPON CODE
    TextColumn::make('coupon_code')
        ->label('Coupon')
        ->getStateUsing(fn ($record) => $record->coupon_code ?? '-'),

    // ✅ COUPON DISCOUNT
    TextColumn::make('coupon_discount')
        ->label('Discount')
        ->money('INR')
        ->getStateUsing(fn ($record) => $record->coupon_discount ?? 0),

    // ✅ TOTAL AMOUNT
    TextColumn::make('total_amount')
        ->label('Total Amount')
        ->money('INR')
        ->sortable(),

    // ✅ ORDER DATE
    TextColumn::make('created_at')
        ->label('Order Date')
        ->dateTime()
        ->sortable(),

    // ✅ STATUS
    TextColumn::make('status')
        ->badge()
        ->colors([
            'warning' => 'pending',
            'primary' => 'processing',
            'info' => 'dispatched',
            'success' => 'delivered',
            'danger' => 'cancelled',
        ]),
])

            ->filters([
                SelectFilter::make('status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'dispatched' => 'Dispatched',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
            ])

            ->recordActions([

                Action::make('view_invoice')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->modalHeading('Invoice')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('invoice.simple-invoice', [
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