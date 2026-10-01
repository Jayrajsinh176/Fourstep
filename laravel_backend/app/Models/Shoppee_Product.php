<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Shoppee_Product extends Model
{
    use HasFactory;
    protected $table = 'shoppee_products';
    protected $fillable = [
        'category',
        'productname',
        'pv',
        'mrp',
        'offerprice',
         'minimum_quantity',
        'commission'
    ];
}