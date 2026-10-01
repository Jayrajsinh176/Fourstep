<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilySaverBonus extends Model
{
    use HasFactory;

    protected $table = 'family_saver_bonuses';

    protected $fillable = [
        'nominee_member_id',
        'deceased_member_id',
        'death_claim_id',
        'month_key',
        'monthly_company_bv',
        'bonus_percentage',
        'bonus_amount',
        'qualification_status',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'monthly_company_pv' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];


    public function nominee()
    {
        return $this->belongsTo(Member::class, 'nominee_member_id');
    }


    public function deceased()
    {
        return $this->belongsTo(Member::class, 'deceased_member_id');
    }


    public function deathClaim()
    {
        return $this->belongsTo(DeathClaim::class, 'death_claim_id');
    }
    
    
}