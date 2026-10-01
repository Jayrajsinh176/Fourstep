<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HelpTicket;
use App\Models\Member;
use App\Models\Memberecom;

class HelpTicketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required',
            'category' => 'required',
            'subject' => 'required',
            'details' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $memberId = $request->member_id;

        /*
        |--------------------------------------------------------------------------
        | Find Member
        |--------------------------------------------------------------------------
        | First check E-commerce member.
        | If not found, check MLM member.
        | Do NOT create an E-commerce member for an MLM member.
        */

        $ecomMember = Memberecom::find($memberId);

        if ($ecomMember) {
            $memberType = 'ecommerce';
        } else {
            $mlmMember = Member::find($memberId);

            if ($mlmMember) {
                $memberType = 'mlm';
            } else {
                return response()->json([
                    'message' => 'The selected member id is invalid.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Image
        |--------------------------------------------------------------------------
        */

        $imagePath = null;

        if ($request->hasFile('image')) {

            $file = $request->file('image');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $destinationPath = public_path('tickets');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $fileName);

            $imagePath = 'tickets/' . $fileName;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Ticket
        |--------------------------------------------------------------------------
        */

        $ticket = HelpTicket::create([
            'member_id' => $memberId,
            'member_type' => $memberType,
            'category' => $request->category,
            'subject' => $request->subject,
            'details' => $request->details,
            'image' => $imagePath,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Ticket submitted successfully',
            'data' => $ticket
        ]);
    }

    public function getTickets($member_id)
    {
        return HelpTicket::where('member_id', $member_id)
            ->latest()
            ->get();
    }
}