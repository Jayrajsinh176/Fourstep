<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoppee_Order extends Model
{
    use HasFactory;

    protected $table = 'shoppee_orders';

    protected $fillable = [

    'order_no',

    'pickup_order_id',

    'distributor_id',

    'user_id',

    'shoppee_member_id',

    'customer_name',

    'order_date',

    'order_type',

    'status',

    'total_amount'
];

    // ORDER ITEMS
   public function pickupOrder()
{
    return $this->belongsTo(
        \App\Models\Orderecom::class,
        'pickup_order_id',
        'id'
    );
}
}