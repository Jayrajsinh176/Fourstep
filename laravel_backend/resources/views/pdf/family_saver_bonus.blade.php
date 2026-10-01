@extends('pdf.layouts.report')

@section('title')
Family Saver Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
            <div class="summary-title">Total Company BV</div>
            <div class="summary-value">
                {{ number_format($totalCompanyBv, 2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Bonus</div>
            <div class="summary-value">
                ₹ {{ number_format($totalBonus, 2) }}
            </div>
        </td>

    </tr>

</table>

@endsection


@section('content')

<table class="data-table">

    <thead>

        <tr>
            <th>Sr.</th>
            <th>Nominee ID</th>
            <th>Nominee Name</th>
            <th>Deceased ID</th>
            <th>Deceased Name</th>
            <th>Bonus Month</th>
            <th>Company BV</th>
            <th>Bonus %</th>
            <th>Bonus Amount</th>
            <th>Qualification</th>
            <th>Status</th>
        </tr>

    </thead>


    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">
                {{ $loop->iteration }}
            </td>

            <td>
                {{ $report->nominee->user_id ?? '-' }}
            </td>

            <td>
                {{ $report->nominee->fullname ?? '-' }}
            </td>

            <td>
                {{ $report->deceased->user_id ?? '-' }}
            </td>

            <td>
                {{ $report->deceased->fullname ?? '-' }}
            </td>

            <td class="center">
                {{ $report->month_key }}
            </td>

            <td class="right">
                {{ number_format($report->monthly_company_bv, 2) }}
            </td>

            <td class="center">
                {{ number_format($report->bonus_percentage, 2) }}%
            </td>

            <td class="right">
                ₹ {{ number_format($report->bonus_amount, 2) }}
            </td>

            <td class="center">
                {{ $report->qualification_status }}
            </td>

            <td class="center">
                {{ ucfirst($report->status) }}
            </td>

        </tr>


    @empty

        <tr>
            <td colspan="11" class="center">
                No records found.
            </td>
        </tr>

    @endforelse


    </tbody>


    <tfoot>

        <tr>

            <th colspan="6" class="right">
                Grand Total ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>

            <th class="right">
                {{ number_format($totalCompanyBv, 2) }}
            </th>

            <th></th>

            <th class="right">
                ₹ {{ number_format($totalBonus, 2) }}
            </th>

            <th></th>

            <th></th>

        </tr>

    </tfoot>


</table>

@endsection