<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantecom;
use App\Models\Productecom;


class Shoppee_ProductRequestItem extends Model
{
    protected $table = 'shoppee_product_request_items';

    protected $guarded = [];

    public function product()
    {
       return $this->belongsTo(
    Productecom::class,
    'product_id'
);

    }
    
    public function variant()
{
    return $this->belongsTo(
        ProductVariantecom::class,
        'variant_id'
    );
}
}