<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orderecom;

class MemberOrderController extends Controller
{
    // Order History
    public function orderHistory(Request $request)
    {
        try {

            $memberUserId = $request->header('member-id');

            if (!$memberUserId) {

                return response()->json([
                    'status' => false,
                    'message' => 'Member ID missing'
                ], 401);

            }

    $orders = Orderecom::with('mlmMember')
    ->where(function ($query) use ($memberUserId) {

        $query->where('mlm_member_id', $memberUserId)
              ->orWhere('delivery_member_id', $memberUserId);

    })
    ->latest()
    ->get();

            return response()->json([
                'status' => true,
                'orders' => $orders
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }

    // Invoice
    public function invoice($id, Request $request)
    {
        try {

            $memberUserId = $request->header('member-id');

            if (!$memberUserId) {

                return response()->json([
                    'status' => false,
                    'message' => 'Member ID missing'
                ], 401);

            }

         $order = Orderecom::with([
    'items.product',
    'items.variant',
    'mlmMember'
])
                ->where(function ($query) use ($memberUserId) {

                    $query->where('mlm_member_id', $memberUserId)
                        ->orWhere('delivery_member_id', $memberUserId);

                })
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'order' => $order
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
}