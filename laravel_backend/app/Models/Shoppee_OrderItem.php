<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Shoppee_OrderItem extends Model
{
    use HasFactory;
    protected $table = 'shoppee_order_items';
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity'
    ];
    public function product()
    {
        return $this->belongsTo(Shoppee_Product::class);
    }
}