<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log(
        string $module,
        string $action,
        string $description
    ): void {

        if (! auth()->check()) {
            return;
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'module'      => $module,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
        ]);
    }
}