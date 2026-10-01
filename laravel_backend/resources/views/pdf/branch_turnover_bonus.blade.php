@extends('pdf.layouts.report')

@section('title')
Branch Turnover Bonus Report
@endsection


@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">
                {{ $reports->count() }}
            </div>
        </td>


        <td>
            <div class="summary-title">Total Turnover</div>
            <div class="summary-value">
                ₹ {{ number_format($totalTurnover, 2) }}
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
            <th>Branch Name</th>
            <th>Branch Code</th>
            <th>Bonus Month</th>
            <th>Total Turnover</th>
            <th>Commission %</th>
            <th>Bonus Amount</th>
            <th>Status</th>
            <th>Calculated Date</th>
        </tr>

    </thead>


    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">
                {{ $loop->iteration }}
            </td>


            <td>
                {{ $report->branch->name ?? '-' }}
            </td>


            <td>
                {{ $report->branch->code ?? '-' }}
            </td>


            <td class="center">
                {{ $report->month_key }}
            </td>


            <td class="right">
                ₹ {{ number_format($report->total_turnover, 2) }}
            </td>


            <td class="center">
                {{ number_format($report->commission_percentage, 2) }}%
            </td>


            <td class="right">
                ₹ {{ number_format($report->bonus_amount, 2) }}
            </td>


            <td class="center">
                {{ ucfirst($report->status) }}
            </td>


            <td class="center">
                {{ \Carbon\Carbon::parse($report->calculated_at)->format('d M Y') }}
            </td>


        </tr>


    @empty

        <tr>
            <td colspan="9" class="center">
                No records found.
            </td>
        </tr>

    @endforelse


    </tbody>


    <tfoot>

        <tr>

            <th colspan="4" class="right">
                Grand Total ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>


            <th class="right">
                ₹ {{ number_format($totalTurnover, 2) }}
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