<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupBuiltupBonus extends Model
{
    protected $table = 'group_builtup_bonuses';

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}