<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'ecom_orders';

    protected $fillable = [
        'member_id',
        'product_name',
        'quantity',
        'total_amount',
        'image',
    ];
}
