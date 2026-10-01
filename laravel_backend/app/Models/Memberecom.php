<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memberecom extends Model
{

    use HasFactory;
    protected $table = 'ecom_members';
    
    protected $fillable = [
    'member_id',
    'fullname',
    'dob',
    'gender',
    'email',
    'mobile_no',
    'address',
    'pin_code',
    'state',
    'city',
    'password',
      'forgot_otp',
    'forgot_otp_expiry',
];
public function helpTickets()
{
    return $this->hasMany(\App\Models\HelpTicket::class);
}
}
