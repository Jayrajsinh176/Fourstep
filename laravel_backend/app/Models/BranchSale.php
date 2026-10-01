<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'member_id',
        'sale_amount',
        'sale_date',
        'status',
    ];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
