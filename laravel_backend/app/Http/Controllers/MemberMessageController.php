<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberInboxMessage;
use App\Models\MemberMessage;
use App\Models\MemberOutboxMessage;
use Illuminate\Http\Request;

class MemberMessageController extends Controller
{

    // SEND MESSAGE
    public function compose(Request $request)
    {

        $request->validate([
            'send_to' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'message_details' => 'required|string'
        ]);

        $senderUserId = $request->header('X-Auth-Member') ?? $request->sender_user_id;

        if (!$senderUserId) {
            return response()->json([
                'message' => 'Missing sender member identifier.'
            ],422);
        }

        $sender = Member::where('user_id',$senderUserId)->first();

        if(!$sender){
            return response()->json([
                'message'=>'Sender member not found.'
            ],404);
        }

        $receiverUserId = $request->send_to;
        $receiver = Member::where('user_id',$receiverUserId)->first();

        // MAIN MESSAGE TABLE
        $message = MemberMessage::create([
            'sender_member_id'=>$sender->id,
            'sender_user_id'=>$sender->user_id,
            'receiver_member_id'=>$receiver?->id,
            'receiver_user_id'=>$receiverUserId,
            'subject'=>$request->subject,
            'message_details'=>$request->message_details
        ]);

        // INBOX
        MemberInboxMessage::create([
            'member_message_id'=>$message->id,
            'receiver_member_id'=>$receiver?->id,
            'receiver_user_id'=>$receiverUserId,
            'sender_user_id'=>$sender->user_id,
            'subject'=>$message->subject,
            'message_details'=>$message->message_details,
            'is_read'=>false
        ]);

        // OUTBOX
        MemberOutboxMessage::create([
            'member_message_id'=>$message->id,
            'sender_member_id'=>$sender->id,
            'sender_user_id'=>$sender->user_id,
            'receiver_user_id'=>$receiverUserId,
            'subject'=>$message->subject,
            'message_details'=>$message->message_details,
            'is_read'=>false
        ]);

        return response()->json([
            'message'=>'Message sent successfully',
            'data'=>[
                'id'=>$message->id,
                'send_to'=>$receiverUserId,
                'subject'=>$message->subject,
                'message_details'=>$message->message_details,
                'created_at'=>$message->created_at
            ]
        ],201);

    }



    // INBOX
    public function inbox(Request $request)
    {

        $memberUserId = $request->header('X-Auth-Member') ?? $request->user_id;

        if(!$memberUserId){
            return response()->json([
                'message'=>'Missing member identifier',
                'data'=>[]
            ],422);
        }

        $limit = $request->limit ?? 200;

        $messages = MemberInboxMessage::where('receiver_user_id',$memberUserId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function($m){
                return [
                    'id'=>$m->id,
                    'from'=>$m->sender_user_id,
                    'to'=>$m->receiver_user_id,
                    'subject'=>$m->subject,
                    'message_details'=>$m->message_details,
                    'status'=>$m->is_read ? 'Read':'Unread',
                    'created_at'=>$m->created_at
                ];
            });

        return response()->json([
            'message'=>'Inbox fetched successfully',
            'data'=>$messages
        ]);

    }



    // OUTBOX
    public function outbox(Request $request)
    {

        $memberUserId = $request->header('X-Auth-Member') ?? $request->user_id;

        if(!$memberUserId){
            return response()->json([
                'message'=>'Missing member identifier',
                'data'=>[]
            ],422);
        }

        $limit = $request->limit ?? 200;

        $messages = MemberOutboxMessage::where('sender_user_id',$memberUserId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function($m){
                return [
                    'id'=>$m->id,
                    'from'=>$m->sender_user_id,
                    'to'=>$m->receiver_user_id,
                    'subject'=>$m->subject,
                    'message_details'=>$m->message_details,
                    'status'=>$m->is_read ? 'Read':'Unread',
                    'created_at'=>$m->created_at
                ];
            });

        return response()->json([
            'message'=>'Outbox fetched successfully',
            'data'=>$messages
        ]);

    }

}