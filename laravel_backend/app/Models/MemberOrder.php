<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberOrder extends Model
{
    use HasFactory;

    protected $table = 'ecom_orders'; // ⚠️ confirm this table name

    protected $fillable = [
        'member_id',
        'mlm_member_id',
        'product_name',
        'quantity',
        'payment_method',
        'invoice_id',
        'total_amount',
        'status',
    ];

    protected static function booted()
    {
        static::updated(function ($order) {

            // Run only when status becomes delivered
            if ($order->isDirty('status') && $order->status === 'delivered') {

                // Only MLM orders
                if (!empty($order->mlm_member_id)) {

                    $member = Member::where('user_id', $order->mlm_member_id)->first();

                    if (!$member) return;

                    // Prevent duplicate
                    if ($member->last_order_id == $order->id) return;

                    // ✅ Add PV
                    $member->pv += $order->total_amount;

                    // ✅ Activate
                    if ($member->pv >= 125) {
                        $member->is_active = 1;
                    }

                    // ✅ Package logic
                    if ($member->pv >= 4000) $member->package_step = 6;
                    elseif ($member->pv >= 2000) $member->package_step = 5;
                    elseif ($member->pv >= 1000) $member->package_step = 4;
                    elseif ($member->pv >= 500) $member->package_step = 3;
                    elseif ($member->pv >= 250) $member->package_step = 2;
                    elseif ($member->pv >= 125) $member->package_step = 1;

                    // Save last processed order
                    $member->last_order_id = $order->id;

                    $member->save();
                }
            }
        });
    }
}