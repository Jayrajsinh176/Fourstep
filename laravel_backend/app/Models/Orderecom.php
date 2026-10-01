<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderecom extends Model
{
    use HasFactory;

    protected $table = 'ecom_orders';

    protected $fillable = [

    'member_id',
    'guest_id',

    'quantity',

    'total_amount',
    'payment_method',
    'invoice_id',
    'status',

    'order_type',

    'delivery_type',
    'delivery_address',
    'delivery_name',
    'delivery_member_id',

    'mlm_member_id',

    'wallet_used',
    'wallet_type',
    'wallet_amount',

    'coupon_code',
    'coupon_discount',

    'amount_paid',
];

    // ✅ CORRECT RELATION (VERY IMPORTANT)
    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class, 'order_id');
    }

    // ✅ MEMBER
public function ecomMember()
{
    return $this->belongsTo(\App\Models\Memberecom::class, 'member_id', 'id');
}

public function mlmMember()
{
    return $this->belongsTo(\App\Models\Member::class, 'mlm_member_id', 'user_id');
}

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($order) {
            if ($order->status === 'dispatched' && !$order->dispatched_at) {
                $order->dispatched_at = now();
            }

            if ($order->status === 'delivered' && !$order->delivered_at) {
                $order->delivered_at = now();
            }
        });

        static::updated(function ($order) {

            if ($order->wasChanged('status') && $order->status === 'delivered') {

                if (!empty($order->mlm_member_id)) {

                    $member = \App\Models\Member::where('user_id', $order->mlm_member_id)->first();
                    if (!$member) return;

                    if ($member->last_order_id == $order->id) return;

                    $member->pv += $order->total_amount;

                 if ($member->pv >= 125 && $member->is_active == 0) {

    // Activation allowed only within 30 days
    if ($member->created_at->diffInDays(now()) > 30) {
        return;
    }

    $member->is_active = 1;
    $member->status = 1;
    $member->activation_date = now();
}

                 $oldStep = $member->package_step;

if ($member->pv >= 4000) $member->package_step = 6;
elseif ($member->pv >= 2000) $member->package_step = 5;
elseif ($member->pv >= 1000) $member->package_step = 4;
elseif ($member->pv >= 500) $member->package_step = 3;
elseif ($member->pv >= 250) $member->package_step = 2;
elseif ($member->pv >= 125) $member->package_step = 1;

// Save upgrade date only when the member moves to a higher step
if (
    $oldStep > 0 &&
    $member->package_step > $oldStep &&
    !empty($member->activation_date)

) {
    $member->package_upgraded_at = now();
}

                    $member->last_order_id = $order->id;
                    $member->save();

                    (new self)->distributePV($member->id, $order->total_amount);
                }
            }
        });
    }

    private function distributePV($memberId, $pv)
    {
        while ($memberId) {

            $member = \App\Models\Member::where('id', $memberId)->first();

            if (!$member || !$member->parent_id) break;

            $parent = \App\Models\Member::where('id', $member->parent_id)->first();

            if (!$parent) break;

            if ($member->position === 'left') {
                \App\Models\Member::where('id', $parent->id)->increment('builtup_left_pv', $pv);
            } else {
                \App\Models\Member::where('id', $parent->id)->increment('builtup_right_pv', $pv);
            }

            $memberId = $parent->id;
        }
    }
}