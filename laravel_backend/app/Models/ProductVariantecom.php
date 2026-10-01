<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantecom extends Model
{
    protected $table = 'ecom_product_variants';

    protected $fillable = [
        'product_id',
        'packing_size',
        'batch_no',
        'hsn_code',
        'mfc_date',
        'expiry_date',
        'price',
        'gst_percentage',
        'gst_price',
        'offer_price',
        'discount_percentage',
        'pv',
        'bv',
        'stock',
           'minimum_quantity',
        'cashback',
      'mega_branch_commission',
'mini_branch_commission',
'pincode_branch_commission',
'product_status',

    ];

    
 protected $casts = [

    'price' => 'float',

    'gst_percentage' => 'float',
    'gst_price' => 'float',

    'offer_price' => 'float',

    'pv' => 'float',
    'bv' => 'float',

    'stock' => 'integer',

    'minimum_quantity' => 'integer',

    'cashback' => 'float',
    'mega_branch_commission' => 'float',
'mini_branch_commission' => 'float',
'pincode_branch_commission' => 'float',
];

    public function product()
    {
        return $this->belongsTo(Productecom::class, 'product_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($variant) {

            // ✅ Offer Price Calculation
            $gstAmount = ($variant->gst_price * $variant->gst_percentage) / 100;

            $variant->offer_price = round(
                $variant->gst_price + $gstAmount,
                2
            );

            // ✅ Discount Calculation
            if ($variant->price > 0 && $variant->offer_price < $variant->price) {

               $variant->discount_percentage = round(
    (($variant->price - $variant->offer_price) / $variant->price) * 100,
    2
);

            } else {
                $variant->discount_percentage = 0;
            }
        });
    }
}