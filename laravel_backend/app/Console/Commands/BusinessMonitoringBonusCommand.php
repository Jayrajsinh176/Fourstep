<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BusinessMonitoringBonusController;

class BusinessMonitoringBonusCommand extends Command
{
    protected $signature = 'bonus:business-monitoring';

    protected $description = 'Calculate weekly Business Monitoring Bonus';

    public function handle(BusinessMonitoringBonusController $controller)
    {
        // Business Monitoring runs after the weekly cycle is completed.
        $cycleDate = now()->subWeek()->toDateString();

        $result = $controller->runCycle($cycleDate);

        $this->info(
            'Business Monitoring bonus calculated successfully.'
        );

        $this->line(
            'Week Start: ' .
            ($result['week_start'] ?? '-')
        );

        $this->line(
            'Week End: ' .
            ($result['week_end'] ?? '-')
        );

        $this->line(
            'Generated Rows: ' .
            ($result['generated_rows'] ?? 0)
        );

        $this->line(
            'Inserted Rows: ' .
            ($result['inserted_rows'] ?? 0)
        );

        $this->line(
            'Total Direct Team Income: ₹' .
            number_format(
                (float) ($result['total_direct_team_income'] ?? 0),
                2
            )
        );

        $this->line(
            'Total Monitoring Bonus: ₹' .
            number_format(
                (float) ($result['total_monitoring_bonus'] ?? 0),
                2
            )
        );

        return Command::SUCCESS;
    }
}