<?php

namespace App\Exports;

use App\Models\PayoutDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TdsReportExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    protected $batchIds;

    public function __construct($batchIds)
    {
        $this->batchIds = $batchIds;
    }

    public function collection()
    {
        return PayoutDetail::with(['member', 'kyc'])
            ->whereIn('batch_id', $this->batchIds)
            ->where('payment_status', 'paid')
            ->orderBy('paid_at')
            ->get()
            ->map(function ($row) {

                return [

                    optional($row->paid_at)->format('d-m-Y'),

                    (string) ($row->member?->user_id ?? ''),

                    $row->member?->fullname,

                    (string) ($row->member?->mobile_no ?? ''),

                    (string) ($row->kyc?->pan_number ?? ''),

                    $row->gross_amount,

                    $row->tds,

                    $row->net_amount,

                ];

            });
    }

    public function headings(): array
    {
        return [

            'Payment Date',
            'Member ID',
            'Member Name',
            'Mobile',
            'PAN',
            'Gross Amount',
            'TDS',
            'Net Amount',

        ];
    }

    public function columnFormats(): array
    {
        return [

            'B' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,

        ];
    }
}