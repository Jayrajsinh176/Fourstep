<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'commission_percentage',
        'is_active',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(BranchSale::class, 'branch_id');
    }

    public function turnoverBonuses()
    {
        return $this->hasMany(BranchTurnoverBonus::class, 'branch_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }
}
