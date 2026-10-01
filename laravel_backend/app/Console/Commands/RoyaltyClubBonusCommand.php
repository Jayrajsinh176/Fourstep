<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RoyaltyClubBonusController;
use Illuminate\Http\Request;

class RoyaltyClubBonusCommand extends Command
{
    protected $signature = 'bonus:royalty-club';

    protected $description = 'Calculate monthly Royalty Club Bonus';

    public function handle(RoyaltyClubBonusController $controller)
    {
       $month = now()->subMonth()->format('Y-m');

        $request = Request::create(
            '/api/royalty-club/calculate-monthly',
            'POST',
            [
                'month' => $month,
            ]
        );

        $response = $controller->calculateMonthly($request);

        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            $this->error(
                $data['message'] ?? 'Royalty Club Bonus calculation failed.'
            );

            return Command::FAILURE;
        }

        $this->info(
            $data['message'] ?? 'Royalty Club Bonus calculated successfully.'
        );

        if (isset($data['data'])) {
            $this->line(
                'Month: ' .
                ($data['data']['month'] ?? $month)
            );

            $this->line(
                'Eligible Members: ' .
                ($data['data']['eligible_users_count'] ?? 0)
            );

            $this->line(
                'Royalty Pool: ₹' .
                number_format(
                    (float) ($data['data']['royalty_pool_amount'] ?? 0),
                    2
                )
            );

            $this->line(
                'Per User Bonus: ₹' .
                number_format(
                    (float) ($data['data']['per_user_bonus'] ?? 0),
                    2
                )
            );
        }

        return Command::SUCCESS;
    }
}