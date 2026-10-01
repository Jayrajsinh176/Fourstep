<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Shoppee_BalanceRequest extends Model
{
    use HasFactory;

    protected $table = 'shoppee_balance_requests'; // ← added

   protected $fillable = [
        'member_id',
        'type',
        'amount',
        'mode_of_payment',
        'transaction_no',
        'payment_slip',
        'status',
           'reject_reason',
    ];

    public function member()
    {
        return $this->belongsTo(\App\Models\Shoppee_Member::class, 'member_id');
    }
}