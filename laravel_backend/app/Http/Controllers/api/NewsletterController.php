<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Newsletter;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
            ]);

            $email = strtolower(trim($request->email));

            $subscriber = Newsletter::where('email', $email)->first();

            if ($subscriber) {
                return response()->json([
                    'message' => 'Email already subscribed.',
                ], 409);
            }

            Newsletter::create([
                'email'  => $email,
                'status' => 'Subscribed',
                'source' => 'Website Footer',
            ]);

            return response()->json([
                'message' => 'Subscribed successfully.',
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Subscription failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}