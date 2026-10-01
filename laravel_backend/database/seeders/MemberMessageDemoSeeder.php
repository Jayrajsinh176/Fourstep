<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MemberInboxMessage;
use App\Models\MemberMessage;
use App\Models\MemberOutboxMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberMessageDemoSeeder extends Seeder
{
   
    public function run(): void
    {
        $primaryUserId = env('MAIN_MEMBER_USER_ID', 'MAINDEV001');

        $primaryMember = Member::firstOrCreate(
            ['user_id' => $primaryUserId],
            [
                'fullname' => 'Main Sponsor',
                'dob' => '1980-01-01',
                'gender' => 'other',
                'mobile_no' => '9999999999',
                'email' => 'main@example.com',
                'password' => Hash::make(env('MAIN_MEMBER_PASSWORD', 'ChangeMe123')),
                'status' => 1,
                'activation_date' => now(),
            ]
        );

        $demoMember = Member::firstOrCreate(
            ['user_id' => 'DEMOUSER02'],
            [
                'fullname' => 'Demo Receiver',
                'dob' => '1995-05-15',
                'gender' => 'female',
                'mobile_no' => '8888888888',
                'email' => 'demo.receiver@example.com',
                'password' => Hash::make('ChangeMe123'),
                'status' => 1,
                'activation_date' => now(),
            ]
        );

        $messages = [
            [
                'sender' => $primaryMember,
                'receiver' => $demoMember,
                'subject' => 'Welcome to the platform!',
                'message_details' => 'This is demo static content stored in DB and shown dynamically in Outbox/Inbox.',
                'is_read' => true,
            ],
            [
                'sender' => $demoMember,
                'receiver' => $primaryMember,
                'subject' => 'Thank you for onboarding',
                'message_details' => 'Thanks, I received your message. This is a seeded static demo reply.',
                'is_read' => false,
            ],
            [
                'sender' => $primaryMember,
                'receiver' => $primaryMember,
                'subject' => 'Self test message',
                'message_details' => 'This self-message helps demo both inbox and outbox for same account.',
                'is_read' => false,
            ],
        ];

        foreach ($messages as $item) {
            $master = MemberMessage::firstOrCreate(
                [
                    'sender_user_id' => $item['sender']->user_id,
                    'receiver_user_id' => $item['receiver']->user_id,
                    'subject' => $item['subject'],
                ],
                [
                    'sender_member_id' => $item['sender']->id,
                    'receiver_member_id' => $item['receiver']->id,
                    'message_details' => $item['message_details'],
                    'is_read' => $item['is_read'],
                    'read_at' => $item['is_read'] ? now()->subDay() : null,
                ]
            );

            MemberInboxMessage::firstOrCreate(
                ['member_message_id' => $master->id],
                [
                    'receiver_member_id' => $item['receiver']->id,
                    'receiver_user_id' => $item['receiver']->user_id,
                    'sender_user_id' => $item['sender']->user_id,
                    'subject' => $master->subject,
                    'message_details' => $master->message_details,
                    'is_read' => $master->is_read,
                    'read_at' => $master->read_at,
                ]
            );

            MemberOutboxMessage::firstOrCreate(
                ['member_message_id' => $master->id],
                [
                    'sender_member_id' => $item['sender']->id,
                    'sender_user_id' => $item['sender']->user_id,
                    'receiver_user_id' => $item['receiver']->user_id,
                    'subject' => $master->subject,
                    'message_details' => $master->message_details,
                    'is_read' => $master->is_read,
                    'read_at' => $master->read_at,
                ]
            );
        }

        $allMembers = Member::query()->get();

        foreach ($allMembers as $member) {
            $master = MemberMessage::firstOrCreate(
                [
                    'sender_user_id' => $member->user_id,
                    'receiver_user_id' => $member->user_id,
                    'subject' => 'Demo Message for ' . $member->user_id,
                ],
                [
                    'sender_member_id' => $member->id,
                    'receiver_member_id' => $member->id,
                    'message_details' => 'Static demo row for dynamic inbox/outbox preview.',
                    'is_read' => false,
                    'read_at' => null,
                ]
            );

            MemberInboxMessage::firstOrCreate(
                ['member_message_id' => $master->id],
                [
                    'receiver_member_id' => $member->id,
                    'receiver_user_id' => $member->user_id,
                    'sender_user_id' => $member->user_id,
                    'subject' => $master->subject,
                    'message_details' => $master->message_details,
                    'is_read' => $master->is_read,
                    'read_at' => $master->read_at,
                ]
            );

            MemberOutboxMessage::firstOrCreate(
                ['member_message_id' => $master->id],
                [
                    'sender_member_id' => $member->id,
                    'sender_user_id' => $member->user_id,
                    'receiver_user_id' => $member->user_id,
                    'subject' => $master->subject,
                    'message_details' => $master->message_details,
                    'is_read' => $master->is_read,
                    'read_at' => $master->read_at,
                ]
            );
        }
    }
}
