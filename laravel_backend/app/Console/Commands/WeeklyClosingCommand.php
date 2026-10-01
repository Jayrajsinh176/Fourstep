<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeeklyClosingService;

class WeeklyClosingCommand extends Command
{
protected $signature = 'income:weekly-closing {closing_date?}';

protected $description = 'Process weekly closing for Purchase Bonus and Repurchase Bonus';

   public function handle(WeeklyClosingService $service)
{
    $closingDate = $this->argument('closing_date') ?: now()->toDateString();

    $result = $service->run($closingDate);

    if (!$result['status']) {
        $this->warn($result['message']);
        return Command::SUCCESS;
    }

    $this->info($result['message']);

    $this->line('Processed: ' . ($result['processed'] ?? 0));

    $this->line(
        'Total Income: ₹' .
        number_format((float) ($result['total_income'] ?? 0), 2)
    );

    return Command::SUCCESS;
}
}