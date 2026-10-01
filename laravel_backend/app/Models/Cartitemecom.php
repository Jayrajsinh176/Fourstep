<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Productecom;

class Cartitemecom extends Model
{
    use HasFactory;

    protected $table = 'ecom_cart_items';

 protected $fillable = [
    'member_id',
    'guest_id',
    'product_id',
    'variant_id',
    'quantity',
];

    // ✅ Cart → Product
    public function product()
    {
        return $this->belongsTo(Productecom::class, 'product_id');
    }
}