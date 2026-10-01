<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class WeeklyCycleLock
{
    public function handle(Request $request, Closure $next): Response
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | WEEKLY MLM CYCLE LOCK
        |--------------------------------------------------------------------------
        |
        | Every Sunday:
        |
        | 12:00 AM        = Normal
        | 12:01 AM–12:10 AM = LOCKED
        | 12:11 AM onwards = UNLOCKED
        |
        */

        $isLocked =
            $now->dayOfWeek === Carbon::SUNDAY &&
            $now->format('H:i') >= '00:00' &&
            $now->format('H:i') <= '00:11';

        if ($isLocked) {
            return response()->json([
                'status' => false,
                'cycle_locked' => true,
                'message' => 'Weekly cycle is running. Member services are temporarily unavailable. Please try again after 12:10 AM.',
            ], 503);
        }

        return $next($request);
    }
}