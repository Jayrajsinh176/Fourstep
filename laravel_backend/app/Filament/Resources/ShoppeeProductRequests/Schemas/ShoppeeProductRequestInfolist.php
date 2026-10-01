<?php

namespace App\Filament\Resources\ShoppeeProductRequests\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ShoppeeProductRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // HEADER SECTION
                Section::make('Product Request Details')
                    ->description('View complete product request information')

                    ->schema([

                        Grid::make(2)
                            ->schema([

                                // ✅ MULTIPLE PRODUCTS
                              TextEntry::make('products')
    ->label('Products')
    ->html()
    ->getStateUsing(function ($record) {

        return $record->items
            ->map(function ($item) {

                return '
                    <div style="margin-bottom:16px;">

                       <div style="
    font-weight:700;
    font-size:15px;
    color:#111827;
">
    ' . ($item->product?->name ?? 'N/A') . '

    <span style="
        font-size:12px;
        color:#2563eb;
        font-weight:600;
    ">
        (' . ($item->variant?->packing_size ?? 'No Size') . ')
    </span>
</div>

                        <div style="
                            margin-top:2px;
                            font-size:13px;
                            color:#6b7280;
                        ">
                            Quantity: ' . $item->quantity . '
                        </div>

                    </div>
                ';

            })
            ->implode('');
    }),

                                // ✅ STATUS
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn ($state) => match (strtolower((string) $state)) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'gray',
                                        default => 'gray',
                                    }),

                                // ✅ TOTAL PRODUCTS
                                TextEntry::make('total_products')
                                    ->label('Total Products')
                                    ->badge()
                                    ->color('info'),

                                // ✅ TOTAL QUANTITY
                                TextEntry::make('total_quantity')
                                    ->label('Total Quantity')
                                    ->getStateUsing(fn ($record) =>
                                        $record->items->sum('quantity')
                                    )
                                    ->badge()
                                    ->color('primary'),

                                // ✅ TOTAL PV
                                TextEntry::make('total_pv')
                                    ->label('Total PV')
                                    ->badge()
                                    ->color('warning'),

                                // ✅ TOTAL AMOUNT
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('INR')
                                    ->weight('Bold')
                                    ->color('success'),

                            ]),

                    ]),

                // ACTIVITY SECTION
                Section::make('Activity Details')

                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M Y - h:i A'),

                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime('d M Y - h:i A'),

                            ]),

                    ]),

            ]);
    }
}