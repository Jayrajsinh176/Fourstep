<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantecom;
use App\Models\Categoryecom;

class Productecom extends Model
{
    use HasFactory;

    protected $table = 'ecom_products';

    protected $fillable = [
        'brand',
        'name',
        'short_description',
        'description',
        'image',
        'is_viral',
        'category_id',
        'selected_variant',
    ];

    protected $casts = [
        'image' => 'array',
    ];

    // ✅ CATEGORY RELATION
    public function category()
    {
        return $this->belongsTo(
            Categoryecom::class,
            'category_id'
        );
    }

    // ✅ VARIANTS RELATION
    public function variants()
    {
        return $this->hasMany(
            ProductVariantecom::class,
            'product_id'
        );
    }

public function selectedVariant()
{
    return $this->belongsTo(
        ProductVariantecom::class,
        'selected_variant'
    );
}

    // ✅ ORDER TYPES
    public function orderTypes()
    {
        return $this->belongsToMany(
            \App\Models\OrderTypeEcom::class,
            'order_type_products',
            'product_id',
            'order_type_id'
        );
    }

    // ✅ IMAGE FORMATTER
    public function getImageAttribute($value)
    {
        if (!$value) {
            return [];
        }

        // ✅ Already array
        if (is_array($value)) {

            $files = $value;

        } else {

            // ✅ Decode JSON
            $decoded = json_decode($value, true);

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {

                $files = $decoded;

            } else {

                // ✅ Old single image support
                $files = [$value];
            }
        }

        // ✅ Convert to full URL
        return collect($files)->map(function ($file) {

            if (!$file) {
                return null;
            }

            // ✅ Prevent double URL
            if (str_starts_with($file, 'http')) {
                return $file;
            }

            return asset('storage/' . ltrim($file, '/'));

        })->filter()->values()->toArray();
    }

    // ✅ KEEP OLD IMAGE
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {

            if (empty($product->image)) {

                $product->image =
                    $product->getOriginal('image');
            }
        });
    }
}