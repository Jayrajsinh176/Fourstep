<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpTicket extends Model
{
    protected $fillable = [
        'member_id',
        'member_type',
        'category',
        'subject',
        'details',
        'image',
        'admin_reply',
        'status'
    ];

    public function ecommerceMember()
    {
        return $this->belongsTo(
            Memberecom::class,
            'member_id',
            'id'
        );
    }

    public function mlmMember()
    {
        return $this->belongsTo(
            Member::class,
            'member_id',
            'id'
        );
    }

    public function getMemberAttribute()
    {
        if ($this->member_type === 'mlm') {
            return $this->mlmMember;
        }

        return $this->ecommerceMember;
    }
}