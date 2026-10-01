<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Shoppee_Helpdesk extends Model
{
    protected $table = 'shoppee_helpdesks';
    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'admin_reply',
        'status'
    ];
        public function member()
    {
        return $this->belongsTo(\App\Models\Shoppee_Member::class, 'user_id', 'id');
    }

}