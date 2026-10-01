<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyKyc extends Model
{
    use HasFactory;

    protected $table = 'my_kyc';

    protected $fillable = [
    'member_id',
    'user_id',
    'account_beneficiary_name',
    'account_no',
    'ifs_code',
    'bank_name',
    'branch_name',
    'aadhaar_number', // ✅ FIXED
    'pan_number',
    'bank_passbook_image',   // ✅ ADD
    'aadhaar_image',         // ✅ ADD
    'pan_image',             // ✅ ADD
    'otp_verified',
    'transaction_password_hash',
    'transaction_password_status',
    'transaction_password_checked_at',
    'status',                // ✅ ADD (IMPORTANT)
];
}
