<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipRankSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'rank_name',
        'min_step_level',
        'generation_count',
        'generation_percentages',
        'is_active',
    ];

    protected $casts = [
        'generation_percentages' => 'array',
        'is_active' => 'boolean',
    ];
}
