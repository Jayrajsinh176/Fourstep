<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
    $schedule->command('bonus:group-builtup')
        ->weeklyOn(7, '00:01')
        ->withoutOverlapping();

    $schedule->command('bonus:repurchase')
        ->weeklyOn(7, '00:03')
        ->withoutOverlapping();

    $schedule->command('income:weekly-closing')
        ->weeklyOn(7, '00:05')
        ->withoutOverlapping();

    $schedule->command('bonus:consistency')
        ->monthlyOn(16, '00:01')
        ->withoutOverlapping();

         $schedule->command('bonus:royalty-club')
        ->monthlyOn(1, '00:10')
        ->withoutOverlapping();

        // 12:05 AM
$schedule->command('bonus:family-saver')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping();

    $schedule->command('bonus:business-monitoring')
    ->weeklyOn(7, '00:07')
    ->withoutOverlapping();
    
})

  ->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'weekly.cycle.lock' => \App\Http\Middleware\WeeklyCycleLock::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();