<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoryecom extends Model
{
    protected $table = 'ecom_categories';

    protected $fillable = ['name', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
   public function getImageAttribute($value)
{
    return $value;
}
}
