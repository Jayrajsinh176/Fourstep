<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipGiftBonus extends Model
{
    use HasFactory;

    protected $table = 'leadership_gift_bonuses';

    protected $fillable = [
        'member_id',
        'rank_name',
        'gift_name',
        'qualified_date',
        'status',
    ];

    protected $casts = [
        'qualified_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}