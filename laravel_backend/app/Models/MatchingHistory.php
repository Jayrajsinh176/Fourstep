<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingHistory extends Model
{
    protected $table = 'matching_history';

    protected $fillable = [
        'user_id',
        'left_bv',
        'right_bv',
        'matched_bv',
        'carry_forward_left',
        'carry_forward_right',
        'income_generated',
        'match_date',
    ];
}