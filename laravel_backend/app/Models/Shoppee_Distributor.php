<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Shoppee_Distributor extends Model
{
    protected $table = 'shoppee_distributors';
    protected $fillable = [
        'distributor_id',
        'name',
        'mobile'
    ];
}