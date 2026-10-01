<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
           'variant_id',
        'price',
        'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Productecom::class, 'product_id');
    }
    
    public function variant()
{
    return $this->belongsTo(
        \App\Models\ProductVariantecom::class,
        'variant_id'
    );
}
}