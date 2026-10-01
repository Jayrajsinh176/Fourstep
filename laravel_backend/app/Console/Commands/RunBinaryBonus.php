<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GroupBuiltupBonusController;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RunBinaryBonus extends Command
{
    protected $signature = 'binary:run';
    protected $description = 'Run binary group builtup bonus';

    public function handle()
    {
        $controller = new GroupBuiltupBonusController();

        $request = new Request([
            'cycle_date' => Carbon::today()->toDateString()
        ]);

        $controller->calculateCycle($request);

        $this->info('Binary bonus executed successfully');
    }
}