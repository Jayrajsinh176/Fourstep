<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FamilySaverBonusController;
use Illuminate\Http\Request;

class FamilySaverBonusCommand extends Command
{
    protected $signature = 'bonus:family-saver';

    protected $description = 'Calculate monthly Family Saver Bonus';

    public function handle(FamilySaverBonusController $controller)
    {
        // Family Saver runs on the 1st and calculates the previous month.
        $month = now()->subMonth()->format('Y-m');

        $request = Request::create(
            '/api/family-saver/calculate-monthly',
            'POST',
            [
                'month' => $month,
            ]
        );

        $response = $controller->calculateMonthly($request);

        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            $this->error(
                $data['message'] ?? 'Family Saver Bonus calculation failed.'
            );

            return Command::FAILURE;
        }

        $this->info(
            $data['message'] ?? 'Family Saver Bonus calculated successfully.'
        );

        if (isset($data['data'])) {
            $this->line(
                'Month: ' .
                ($data['data']['month'] ?? $month)
            );

            $this->line(
               'Company BV: ' . 
number_format( 
    (float) ($data['data']['company_bv'] ?? 0), 
    2 
)
            );

            $this->line(
                'Qualified Claims: ' .
                ($data['data']['qualified_claims'] ?? 0)
            );

            $this->line(
                'Bonus Pool: ₹' .
                number_format(
                    (float) ($data['data']['total_bonus_pool'] ?? 0),
                    2
                )
            );

            $this->line(
                'Inserted Rows: ' .
                ($data['data']['inserted_rows'] ?? 0)
            );
        }

        return Command::SUCCESS;
    }
}