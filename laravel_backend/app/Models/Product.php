<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'ecom_products';

    protected $fillable = [
        'brand',
        'name',
        'price',
        'description',
        'image',
        'is_viral',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
