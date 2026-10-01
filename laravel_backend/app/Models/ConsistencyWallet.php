<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsistencyWallet extends Model
{
    protected $table = 'consistency_wallet';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'detail',
        'credit',
        'debit',
        'created_at',
    ];

 public function member()
{
    return $this->belongsTo(Member::class, 'user_id', 'id');
}
}