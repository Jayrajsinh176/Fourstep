<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoppee_Kyc extends Model
{
    use HasFactory;

    protected $table = 'shoppee_kycs';

    protected $fillable = [
        'member_id',
        'member_name',

        'account_beneficiary_name',
        'account_no',
        'ifs_code',
        'bank_name',
        'branch_name',

        'bank_passbook_image',

        'aadhaar_number',
        'aadhaar_image',

        'pan_number',
        'pan_image',

        'status',
        'is_latest',
    ];

    public function member()
    {
        return $this->belongsTo(Shoppee_Member::class, 'member_id');
    }
}