<?php

namespace App\Exports;

use App\Models\PayoutDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PayoutSheetExport implements FromCollection, WithHeadings, WithColumnFormatting
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
            ->orderBy('member_id')
            ->get()
            ->map(function ($row) {

                return [

                    (string) ($row->member?->user_id ?? ''),

                    $row->member?->fullname,

                    (string) ($row->member?->mobile_no ?? ''),

                    (string) ($row->kyc?->pan_number ?? ''),

                    $row->kyc?->account_beneficiary_name,

                    (string) ($row->kyc?->account_no ?? ''),

                    (string) ($row->kyc?->ifs_code ?? ''),

                    $row->kyc?->bank_name,

                    $row->net_amount,

                
                    ucfirst($row->payment_status),

                ];
            });
    }

    public function headings(): array
    {
        return [

            'Member ID',
            'Member Name',
            'Mobile',
            'PAN',
            'Account Holder',
            'Account Number',
            'IFSC',
            'Bank Name',
            'Net Amount',
            'Payment Status',

        ];
    }

    public function columnFormats(): array
    {
        return [

            'A' => NumberFormat::FORMAT_TEXT, // Member ID
            'C' => NumberFormat::FORMAT_TEXT, // Mobile
            'D' => NumberFormat::FORMAT_TEXT, // PAN
            'F' => NumberFormat::FORMAT_TEXT, // Account Number
            'G' => NumberFormat::FORMAT_TEXT, // IFSC

        ];
    }
}