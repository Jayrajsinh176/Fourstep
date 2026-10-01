<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsistencyWalletProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'amount',
        'image_path',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'boolean',
    ];
}
