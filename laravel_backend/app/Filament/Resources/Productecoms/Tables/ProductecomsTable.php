<?php

namespace App\Filament\Resources\Productecoms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;


class ProductecomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
          ->defaultSort('id', 'desc')
           ->columns([

TextColumn::make('sr_no')
        ->label('SR No.')
        ->rowIndex(),
        
    // ✅ CATEGORY
    TextColumn::make('category.name')
        ->label('Category')
        ->searchable(),

    // ✅ BRAND
    TextColumn::make('brand')
        ->searchable(),

    // ✅ PRODUCT NAME
    TextColumn::make('name')
        ->searchable(),
        
        TextColumn::make('short_description')
    ->label('Tagline')
    ->limit(60)
    ->wrap()
    ->tooltip(fn ($record) => $record->short_description),
    
// ✅ ORDER TYPES
TextColumn::make('orderTypes.name')

    ->label('Order Types')

    ->badge()

    ->separator(',')

    ->color('success'),
      // ✅ IMAGE
    ViewColumn::make('image')
        ->label('Image')
        ->view('filament.pages.single-image'),

        // ✅ DESCRIPTION
TextColumn::make('description')
    ->limit(40)
    ->wrap(),
    // ✅ PACKAGE SIZE DROPDOWN
    SelectColumn::make('selected_variant')
    ->label('Package Size')

    ->options(function ($record) {

        return $record->variants
            ->pluck('packing_size', 'id')
            ->toArray();
    })

    ->default(function ($record) {

        return $record->selected_variant
            ?? $record->variants->first()?->id;
    })

    ->selectablePlaceholder(false)

    ->afterStateUpdated(function ($state, $record) {

        $record->update([
            'selected_variant' => $state
        ]);
    }),

 // ✅ BATCH
TextColumn::make('variant_batch')
    ->label('Batch')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->batch_no ?? '-';
    }),

    // ✅ HSN CODE
TextColumn::make('variant_hsn_code')
    ->label('HSN Code')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->hsn_code ?? '-';
    }),

// ✅ MFC
TextColumn::make('variant_mfc')
    ->label('MFC')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->mfc_date ?? '-';
    }),

// ✅ EXPIRY
TextColumn::make('variant_expiry')
    ->label('Expiry')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->expiry_date ?? '-';
    }),

// ✅ MRP
TextColumn::make('variant_mrp')
    ->label('MRP')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant
            ? '₹' . number_format($variant->price, 2)
            : '-';
    }),

// ✅ GST %
TextColumn::make('variant_gst')
    ->label('GST')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

      return $variant
    ? $variant->gst_percentage . '%'
    : '-';
    }),

// ✅ GST PRICE
TextColumn::make('variant_gst_price')
    ->label('Without GST Price')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return '₹' . number_format($variant->gst_price ?? 0, 2);
    }),

// ✅ OFFER
TextColumn::make('variant_offer')
    ->label('Offer')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return '₹' . number_format($variant->offer_price ?? 0, 2);
    }),

// ✅ DISCOUNT
TextColumn::make('variant_discount')
    ->label('Discount')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return ($variant->discount_percentage ?? 0) . '%';
    }),

// ✅ PV
// TextColumn::make('variant_pv')
//     ->label('PV')
//     ->state(function ($record) {

//         $variant = $record->variants
//             ->where('id', $record->selected_variant)
//             ->first()

//             ?? $record->variants->first();

//         return $variant->pv ?? 0;
//     }),

// ✅ BV
TextColumn::make('variant_bv')
    ->label('BV')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->bv ?? 0;
    }),

// ✅ STOCK
TextColumn::make('variant_stock')
    ->label('Stock')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->stock ?? 0;
    }),
    
    // ✅ MINIMUM QUANTITY
TextColumn::make('variant_minimum_quantity')
    ->label('Min Qty')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant->minimum_quantity ?? 1;
    }),
// ✅ CASHBACK
TextColumn::make('variant_cashback')
    ->label('Cashback')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return '₹' . number_format($variant->cashback ?? 0, 2);
    }),
    

// ✅ MEGA COMMISSION
TextColumn::make('variant_mega_commission')
    ->label('Mega %')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return ($variant->mega_branch_commission ?? 0) . '%';
    }),

// ✅ MINI COMMISSION
TextColumn::make('variant_mini_commission')
    ->label('Mini %')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return ($variant->mini_branch_commission ?? 0) . '%';
    }),

// ✅ PINCODE COMMISSION
TextColumn::make('variant_pincode_commission')
    ->label('Home %')
    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return ($variant->pincode_branch_commission ?? 0) . '%';
    }),

    
// ✅ PRODUCT STATUS
TextColumn::make('variant_status')
    ->label('Status')
    ->badge()

    ->color(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return match ($variant->product_status ?? '') {

            'coming_soon' => 'gray',

            'advanced_booking' => 'warning',

            default => 'success',
        };
    })

    ->state(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return match ($variant->product_status ?? '') {

            'coming_soon' => 'Coming Soon',

            'advanced_booking' => 'Advanced Booking',

            default => 'Order Now',
        };
    }),
    

    // ✅ TRENDING
    IconColumn::make('is_viral')
        ->label('Trending')
        ->boolean(),

])

            ->filters([
                //
            ])

            ->recordActions([

                // ✅ VIEW ACTIVE VARIANT
Action::make('view')
    ->label('View')
    ->icon('heroicon-o-eye')

    ->modalHeading(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return $variant
            ? $record->name . ' - ' . $variant->packing_size
            : $record->name;
    })

    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Close')

    ->modalContent(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return view(
            'filament.pages.product-view',
            [
                'product' => $record,
                'variant' => $variant,
            ]
        );
    }),

// ✅ EDIT ACTIVE VARIANT
Action::make('edit')
    ->label('Edit')
    ->icon('heroicon-o-pencil-square')

    ->url(function ($record) {

        $variant = $record->variants
            ->where('id', $record->selected_variant)
            ->first()

            ?? $record->variants->first();

        return route(
            'filament.admin.resources.productecoms.edit',
            [
                'record' => $record->id,
                'variant' => $variant?->id,
            ]
        );
    }),

            ])

            ->toolbarActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

            ]);
    }
}