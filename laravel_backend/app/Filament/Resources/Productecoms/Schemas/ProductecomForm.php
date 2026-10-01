<?php

namespace App\Filament\Resources\Productecoms\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class ProductecomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

        
            Forms\Components\TextInput::make('name')
                ->required(),
                
                Forms\Components\Textarea::make('short_description')
    ->label('Product Tagline')
    ->rows(3)
    ->placeholder('Deep Hydration & Nourishment for Soft, Smooth & Healthy Looking Skin')
    ->columnSpanFull(),

            Forms\Components\TextInput::make('brand')
                ->required(),

            Forms\Components\Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),
// ✅ ORDER TYPES
Forms\Components\Select::make('orderTypes')

    ->label('Order Types')

    ->relationship(
        name: 'orderTypes',
        titleAttribute: 'name'
    )

    ->multiple()

    ->preload()

    ->searchable(),
    
          
                
    
    
   // ✅ MULTIPLE IMAGE + VIDEO UPLOAD
          Forms\Components\FileUpload::make('image')
    ->label('Product Media')
    ->multiple()
    ->image()
       ->panelLayout('grid')
    ->downloadable()
    ->maxFiles(12)
    ->acceptedFileTypes([
        'image/jpeg',
        'image/png',
        'image/webp',
        'video/mp4',
        'video/quicktime'
    ])
    ->helperText('Upload up to 12 images. Maximum file size: 2 MB per image.')
// ->maxSize(2048)
    ->disk('public')
    ->directory('product')
    ->visibility('public')
    ->reorderable()
    ->imagePreviewHeight('80')
    ->preserveFilenames()
  ->required()
   ->formatStateUsing(function ($state) {

    if (! $state) {
        return [];
    }

    if (! is_array($state)) {
        $state = json_decode($state, true) ?: [$state];
    }

    return collect($state)
        ->map(function ($file) {

            // https://fourstepretail.com/storage/product/563843.jpg
            if (str_contains($file, '/storage/')) {
                return str_replace(
                    asset('storage') . '/',
                    '',
                    $file
                );
            }

            // old images format
            if (str_contains($file, '/images/product/')) {
                return 'product/' . basename($file);
            }

            return $file;
        })
        ->toArray();
})
                ->dehydrateStateUsing(function ($state) {

                    return collect($state)->map(function ($file) {

                        return str_replace(
                            url('/storage/') . '/',
                            '',
                            $file
                        );

                    })->toArray();
                })

                ->getUploadedFileNameForStorageUsing(function ($file) {

                    $originalName = $file->getClientOriginalName();

                    return str_replace(
                        ' ',
                        '-',
                        $originalName
                    );
                }),

            Forms\Components\Placeholder::make('current_images')
    ->visible(fn ($record) => filled($record))
    ->label('Current Images')
    ->content(function ($record) {

        if (! $record || empty($record->image)) {
            return 'No images';
        }

        return new HtmlString(
            '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;">'
            .
            collect($record->image)
    ->map(fn ($img) => '
        <a href="' . $img . '" target="_blank">
            <img
                src="' . $img . '"
                style="
                    width:120px;
                    height:120px;
                    object-fit:cover;
                    border-radius:8px;
                    border:1px solid #ddd;
                "
            >
        </a>
    ')
    ->implode('')
            .
            '</div>'
        );
    }),
    
         
  Forms\Components\Textarea::make('description')
                ->required(), 
                
          Forms\Components\Repeater::make('variants')

    ->relationship('variants')

    ->label('Package Variants')

    ->columnSpanFull()

    ->collapsed(function ($state) {

        if (!request()->has('variant')) {
            return true;
        }

        return ($state['id'] ?? null) != request()->get('variant');
    })
    ->itemLabel(function (array $state): ?string {

    $size = $state['packing_size'] ?? 'Variant';

    $status = $state['product_status'] ?? '';

    return "{$size} ({$status})";
})
                ->schema([

                    Forms\Components\TextInput::make('packing_size')
                        ->placeholder('500gm')
                        ->required(),

                    Forms\Components\TextInput::make('batch_no')
                     ->required(),

                     Forms\Components\TextInput::make('hsn_code')
    ->label('HSN Code')
    ->required()
    ->maxLength(20),

                    Forms\Components\TextInput::make('stock')
                        ->numeric()
                         ->required()
                        ->default(0),
                        
                        Forms\Components\TextInput::make('minimum_quantity')
    ->label('Minimum Qty')
     ->required()
    ->numeric()
    ->default(1), 
                    Forms\Components\TextInput::make('mfc_date')
                        ->placeholder('05/2026'),

                    Forms\Components\TextInput::make('expiry_date')
                        ->placeholder('05/2028'),

                 Forms\Components\TextInput::make('price')
    ->label('MRP')
    ->numeric()
    ->prefix('₹')
    ->live()
    ->required()
    ->afterStateUpdated(function ($state, callable $get, callable $set) {

        $gstPrice = (float) $get('gst_price');
        $gstPercentage = (float) $get('gst_percentage');

        $offerPrice = round(
            $gstPrice + (($gstPrice * $gstPercentage) / 100),
            2
        );

        $set('offer_price', $offerPrice);

   $discount = $state > 0
    ? round((($state - $offerPrice) / $state) * 100, 2)
    : 0;

        $set('discount_percentage', max($discount, 0));
    }),
                Forms\Components\Select::make('gst_percentage')
    ->options([
        '0.00' => '0%',
        '5.00' => '5%',
        '12.00' => '12%',
        '18.00' => '18%',
        '28.00' => '28%',
    ])
    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
    ->live()
    ->afterStateUpdated(function ($state, callable $get, callable $set) {

        $gstPrice = (float) $get('gst_price');
        $mrp = (float) $get('price');

        $offerPrice = round(
            $gstPrice + (($gstPrice * (float) $state) / 100),
            2
        );

        $set('offer_price', $offerPrice);

  $discount = $mrp > 0
    ? round((($mrp - $offerPrice) / $mrp) * 100, 2)
    : 0;

        $set('discount_percentage', max($discount, 0));
    })
    ->default('0.00')
    ->required(),

                  Forms\Components\TextInput::make('gst_price')
                      ->label('Without GST Price')
    
    ->numeric()
    ->prefix('₹')
    ->live()
    ->required()
    ->afterStateUpdated(function ($state, callable $get, callable $set) {

        $gstPercentage = (float) $get('gst_percentage');
        $mrp = (float) $get('price');

        $offerPrice = round(
            $state + (($state * $gstPercentage) / 100),
            2
        );

        $set('offer_price', $offerPrice);

    $discount = $mrp > 0
    ? round((($mrp - $offerPrice) / $mrp) * 100, 2)
    : 0;

        $set('discount_percentage', max($discount, 0));
    }),

                    Forms\Components\TextInput::make('offer_price')
                        ->disabled(),

                    Forms\Components\TextInput::make('discount_percentage')
                    ->label('discount %')
                        ->disabled(),

                    // Forms\Components\TextInput::make('pv')
                    //     ->numeric()
                    //     ->default(0),

                    Forms\Components\TextInput::make('bv')
                        ->numeric()
                        ->default(0),

                    // ✅ CASHBACK
                    Forms\Components\TextInput::make('cashback')
                        ->numeric()
                        ->default(0),
                        
                      Forms\Components\TextInput::make('mega_branch_commission')
    ->label('Mega Branch %')
    ->numeric()
    ->default(0)
    ->suffix('%'),

Forms\Components\TextInput::make('mini_branch_commission')
    ->label('Mini Branch  %')
    ->numeric()
    ->default(0)
    ->suffix('%'),

Forms\Components\TextInput::make('pincode_branch_commission')
    ->label('Home Branch %')
    ->numeric()
    ->default(0)
    ->suffix('%'),
                    // ✅ PRODUCT STATUS
                    Forms\Components\Select::make('product_status')

                        ->options([
                            'coming_soon' => 'Coming Soon',
                            'order_now' => 'Order Now',
                            'advanced_booking' => 'Advanced Booking',
                        ])
 ->default('order_now')
                        ->required(),

                ])
                ->columns(6),

            Forms\Components\Toggle::make('is_viral')
                ->label('Trending Product')
                ->default(false),


        ]);
    }
}