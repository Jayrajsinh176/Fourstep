<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RepurchaseBonusController;

class RepurchaseBonusCommand extends Command
{
    protected $signature = 'bonus:repurchase';

    protected $description = 'Calculate weekly Repurchase Bonus';

    public function handle(RepurchaseBonusController $controller)
    {
        $response = $controller->calculateRepurchaseBonus();

        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            $this->error($data['message'] ?? 'Repurchase Bonus calculation failed.');
            return Command::FAILURE;
        }

        $this->info($data['message'] ?? 'Repurchase Bonus calculated successfully.');

        if (isset($data['data'])) {
            $this->line(
                'Qualified Members: ' .
                ($data['data']['qualified_members'] ?? 0)
            );

            $this->line(
                'Total Bonus: ₹' .
                number_format(
                    (float) ($data['data']['total_bonus'] ?? 0),
                    2
                )
            );
        }

        return Command::SUCCESS;
    }
}