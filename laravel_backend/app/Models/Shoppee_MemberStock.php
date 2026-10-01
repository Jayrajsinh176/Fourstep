<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shoppee_MemberStock extends Model
{
    protected $table =
        'shoppee_member_stocks';

    protected $fillable = [

        'member_id',
        'variant_id',
        'quantity',
    ];
}