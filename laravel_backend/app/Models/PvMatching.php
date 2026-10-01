<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PvMatching extends Model
{
    use HasFactory;

    protected $table = 'pv_matchings';

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
