<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LoyaltyBonusController;

class ConsistencyBonusCommand extends Command
{
    protected $signature = 'bonus:consistency';

    protected $description = 'Calculate monthly Consistency Bonus';

    public function handle(LoyaltyBonusController $controller)
    {
        $response = $controller->calculateConsistencyBonus();

        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            $this->error(
                $data['message'] ?? 'Consistency Bonus calculation failed.'
            );

            return Command::FAILURE;
        }

        $this->info(
            $data['message'] ?? 'Consistency Bonus executed successfully.'
        );

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