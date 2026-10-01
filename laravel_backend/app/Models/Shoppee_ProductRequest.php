<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Shoppee_Product;
use App\Models\Shoppee_ProductRequestItem;
use App\Models\Shoppee_Member;

class Shoppee_ProductRequest extends Model
{
    protected $table = 'shoppee_product_requests';

    protected $fillable = [
        'member_id',
        'product_id',
        'quantity',
        'total_products',
        'total_amount',
        'total_pv',
        'total_bv',
        'status'
    ];

    // OLD SINGLE PRODUCT RELATION
    public function product()
    {
        return $this->belongsTo(
            Shoppee_Product::class,
            'product_id'
        );
    }

    // NEW MULTIPLE PRODUCTS RELATION
    public function items()
    {
        return $this->hasMany(
            Shoppee_ProductRequestItem::class,
            'request_id'
        );
    }

    // MEMBER RELATION
    public function member()
    {
        return $this->belongsTo(
            Shoppee_Member::class,
            'member_id'
        );
    }
}