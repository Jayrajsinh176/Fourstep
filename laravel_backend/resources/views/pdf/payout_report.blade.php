<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 15px;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        h1, h2, h3, p {
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header h3 {
            font-size: 13px;
            margin-top: 3px;
            color: #555;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            table-layout: fixed;
        }

        .summary td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-size: 10px;
        }

        .summary .title {
            background: #f4f4f4;
            font-weight: bold;
            width: 16%;
            color: #444;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.details thead {
            display: table-header-group;
        }

        table.details tr {
            page-break-inside: avoid;
        }

        table.details thead th {
            background: #1a2535;
            color: #fff;
            border: 1px solid #1a2535;
            padding: 5px 3px;
            font-size: 8.5px;
            text-align: center;
            font-weight: 600;
        }

        table.details tbody td {
            border: 1px solid #ddd;
            padding: 4px 3px;
            font-size: 8.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        table.details tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .status-paid {
            color: #1a7f37;
            font-weight: 600;
        }

        .status-pending {
            color: #b45309;
            font-weight: 600;
        }

        .status-failed {
            color: #AE4329;
            font-weight: 600;
        }

        /* Column widths so the table never overflows the page */
        .col-sr    { width: 3%; }
        .col-id    { width: 8%; }
        .col-name  { width: 8%; }
        .col-mobile{ width: 7%; }
        .col-pan   { width: 6%; }
        .col-acc-holder { width: 9%; }
        .col-acc-no { width: 9%; }
        .col-ifsc  { width: 6%; }
        .col-bank  { width: 9%; }
        .col-gross { width: 6%; }
        .col-tds   { width: 5%; }
        .col-admin { width: 5%; }
        .col-net   { width: 6%; }
        .col-ref   { width: 8%; }
        .col-status{ width: 5%; }

        .footer {
            margin-top: 16px;
            font-size: 9px;
        }

        .footer hr {
            border: none;
            border-top: 1px solid #ccc;
            margin-bottom: 8px;
        }

        .generated {
            text-align: right;
            color: #666;
        }
    </style>

</head>

<body>

    <div class="header">
        <h2>FOUR STEP RETAIL PVT. LTD.</h2>
        <h3>PAYOUT REPORT</h3>
    </div>

    <table class="summary">
        <tr>
            <td class="title">Batch No</td>
            <td>{{ $batch->batch_no }}</td>
            <td class="title">Payout Date</td>
            <td>{{ \Carbon\Carbon::parse($batch->payout_date)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="title">Members</td>
            <td>{{ $batch->total_members }}</td>
            <td class="title">Status</td>
            <td>{{ strtoupper($batch->status) }}</td>
        </tr>
        <tr>
            <td class="title">Gross Amount</td>
            <td>&#8377; {{ number_format($totalGross, 2) }}</td>
            <td class="title">Net Amount</td>
            <td>&#8377; {{ number_format($totalNet, 2) }}</td>
        </tr>
        <tr>
            <td class="title">Total TDS</td>
            <td>&#8377; {{ number_format($totalTds, 2) }}</td>
            <td class="title">Admin Charge</td>
            <td>&#8377; {{ number_format($totalAdmin, 2) }}</td>
        </tr>
    </table>

    <table class="details">
        <thead>
            <tr>
                <th class="col-sr">Sr</th>
                <th class="col-id">Member ID</th>
                <th class="col-name">Name</th>
                <th class="col-mobile">Mobile</th>
                <th class="col-pan">PAN</th>
                <th class="col-acc-holder">Account Holder</th>
                <th class="col-acc-no">Account No</th>
                <th class="col-ifsc">IFSC</th>
                <th class="col-bank">Bank</th>
                <th class="col-gross">Gross</th>
                <th class="col-tds">TDS</th>
                <th class="col-admin">Admin</th>
                <th class="col-net">Net</th>
                <th class="col-ref">Reference No</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row->member->user_id ?? '-' }}</td>
                    <td>{{ $row->member->fullname ?? '-' }}</td>
                    <td>{{ $row->member->mobile_no ?? '-' }}</td>
                    <td>{{ $row->kyc->pan_number ?? '-' }}</td>
                    <td>{{ $row->kyc->account_beneficiary_name ?? '-' }}</td>
                    <td>{{ $row->kyc->account_no ?? '-' }}</td>
                    <td>{{ $row->kyc->ifs_code ?? '-' }}</td>
                    <td>{{ $row->kyc->bank_name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($row->gross_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($row->tds, 2) }}</td>
                    <td class="text-right">{{ number_format($row->admin_charge, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($row->net_amount, 2) }}</strong></td>
                    <td>{{ $row->reference_no ?? '-' }}</td>
                    <td class="text-center status-{{ strtolower($row->payment_status) }}">
                        {{ ucfirst($row->payment_status) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <hr>
        <table width="100%">
            <tr>
                <td><strong>FOUR STEP RETAIL PVT. LTD.</strong></td>
                <td class="generated">Generated On : {{ now()->format('d-m-Y h:i A') }}</td>
            </tr>
        </table>
    </div>

</body>

</html>