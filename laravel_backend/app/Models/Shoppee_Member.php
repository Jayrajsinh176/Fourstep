<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Shoppee_Member extends Model
{
    use HasFactory;
    protected $table = 'shoppee_members';
    
   protected $hidden = [
    'password',
    'forgot_password_otp',
];

   protected $fillable = [
    'member_id',
    'fullname',
      'user_pan',
    'aadhaar_no',
    'user_address',

    'branch_name',
    'branch_type',
    'branch_pan',
    'dob',
    'gst_no',
    'email',
    'password',
    
    'transaction_password',
    'transaction_password_otp',
    'transaction_password_otp_expiry',
    
    'forgot_password_otp',
        
    'otp_expiry',
    'mobile_no',
    'address',
    'pin_code',
    'state',
    'city',
    'district',
];
}