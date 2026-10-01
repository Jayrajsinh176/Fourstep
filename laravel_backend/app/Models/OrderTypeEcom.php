<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTypeEcom extends Model
{
    protected $table = 'order_type_ecoms';

    protected $fillable = ['name', 'description', 'status'];

    public function products()
    {
        return $this->belongsToMany(
            Productecom::class,
            'order_type_products',
            'order_type_id',
            'product_id'
        );
    }
}