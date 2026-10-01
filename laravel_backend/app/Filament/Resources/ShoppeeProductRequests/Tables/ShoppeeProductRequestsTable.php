<?php

namespace App\Filament\Resources\ShoppeeProductRequests\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use App\Models\ProductVariantecom;
use App\Models\Shoppee_MemberStock;
use App\Services\ActivityLogService;

class ShoppeeProductRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('member.fullname')
    ->label('Member')
    ->formatStateUsing(function ($state, $record) {

        if (!$record->member) {
            return 'N/A';
        }

        return $record->member->fullname .
            ' (' . $record->member->member_id . ')';

    })
    ->searchable(),

             Tables\Columns\TextColumn::make('products')
    ->label('Products')
    ->getStateUsing(function ($record) {

        return $record->items
            ->map(fn ($item) => $item->product?->name .
' (' .
$item->variant?->packing_size .
')')
            ->filter()
            ->implode(', ');
    })
    ->wrap(),
    
    Tables\Columns\TextColumn::make('total_products')
        ->label('Items')
        ->badge()
        ->color('info'),

                Tables\Columns\TextColumn::make('total_quantity')
    ->label('Quantity')
    ->getStateUsing(fn ($record) =>
        $record->items->sum('quantity')
    )
    ->badge()
    ->color('primary'),


                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_pv'),
                Tables\Columns\TextColumn::make('total_bv')
    ->label('Total BV'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i'),

            ])

            // ✅ FILTERS
            ->filters([

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),

            ])

            ->recordUrl(null)

            ->recordActions([

                // ✅ VIEW
                ViewAction::make()
                    ->label('View')
                    ->modalFooterActions(fn () => []),

                // ✅ APPROVE
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => strtolower($record->status) === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {

    if (strtolower($record->status) !== 'pending') {
        return;
    }

    // CHECK LATEST STOCK
    foreach ($record->items as $item) {

        if (!$item->variant_id) {
            continue;
        }

        $variant = ProductVariantecom::find(
            $item->variant_id
        );

        if (!$variant) {

            \Filament\Notifications\Notification::make()
                ->title('Variant not found')
                ->danger()
                ->send();

            return;
        }

        if ($variant->stock < $item->quantity) {

            \Filament\Notifications\Notification::make()
                ->title(
                    $variant->product->name .
                    ' (' .
                    $variant->packing_size .
                    ') stock is low'
                )
                ->danger()
                ->send();

            return;
        }
    }

    $record->update([
        'status' => 'Approved'
    ]);


    // STOCK UPDATE
foreach ($record->items as $item) {

    if (!$item->variant_id) {
        continue;
    }

    // REDUCE MAIN STOCK
    ProductVariantecom::where(
        'id',
        $item->variant_id
    )->decrement(
        'stock',
        $item->quantity
    );

    // MEMBER STOCK
    $memberStock =
        Shoppee_MemberStock::where(
            'member_id',
            $record->member_id
        )
        ->where(
            'variant_id',
            $item->variant_id
        )
        ->first();

    if ($memberStock) {

        $memberStock->increment(
            'quantity',
            $item->quantity
        );

    } else {

        Shoppee_MemberStock::create([

            'member_id' =>
                $record->member_id,

            'variant_id' =>
                $item->variant_id,

            'quantity' =>
                $item->quantity,
        ]);
    }
}

    // prevent duplicate transaction
    $exists = \App\Models\Shoppee_Transaction::where('ref_id', $record->id)
        ->where('ref_type', 'product_request')
        ->exists();

    if (!$exists) {

$productNames = $record->items
    ->map(fn ($item) => $item->product?->name)
            ->filter()
            ->implode(', ');

        \App\Models\Shoppee_Transaction::create([

            'member_id' => $record->member_id,
            'ref_id' => $record->id,
            'ref_type' => 'product_request',
            'balance_type' => 'purchase',
            'entry_type' => 'debit',
            'amount' => $record->total_amount,

            'detail' =>
                'Debited Against Product Request#' .
                $record->id .
                ' (' . $productNames . ')',
        ]);
        $productNames = $record->items
    ->map(fn ($item) => $item->product?->name)
    ->filter()
    ->implode(', ');

ActivityLogService::log(
    'Shoppee Panel',
    'Product Request Approved',
    'Approved Product Request #' . $record->id .
    ' for ' . $record->member->fullname .
    ' (' . $record->member->member_id . ')' .
    ' | Products: ' . $productNames .
  ' | Amount INR ' . number_format($record->total_amount, 2)
);
    }
}),

                // ❌ REJECT
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => strtolower($record->status) === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {

    $record->update([
        'status' => 'Rejected'
    ]);

    $productNames = $record->items
        ->map(fn ($item) => $item->product?->name)
        ->filter()
        ->implode(', ');

    ActivityLogService::log(
        'Shoppee Panel',
        'Product Request Rejected',
        'Rejected Product Request #' . $record->id .
        ' for ' . $record->member->fullname .
        ' (' . $record->member->member_id . ')' .
        ' | Products: ' . $productNames .
      ' | Amount INR ' . number_format($record->total_amount, 2)
    );
}),

            ])

            ->toolbarActions([])

            ->bulkActions([

                DeleteBulkAction::make()
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(),

            ]);
    }
}