@extends('pdf.layouts.report')

@section('title')
Rank Reward Report
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
            <div class="summary-title">Approved Rewards</div>
            <div class="summary-value">
                {{ $reports->where('status', 'approved')->count() }}
            </div>
        </td>

        <td>
            <div class="summary-title">Delivered Rewards</div>
            <div class="summary-value">
                {{ $reports->where('status', 'delivered')->count() }}
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
            <th>Member ID</th>
            <th>Member Name</th>
            <th>Rank Reward</th>
            <th>Achievement Date</th>
            <th>Status</th>
     <th>Target Amount</th>
        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">
                {{ $loop->iteration }}
            </td>

            <td>
                {{ $report->member->user_id ?? '-' }}
            </td>

            <td>
                {{ $report->member->fullname ?? '-' }}
            </td>

            <td>
                {{ $report->reward->rank_name ?? '-' }}
            </td>

            <td class="center">
                {{ $report->achieved_at ? \Carbon\Carbon::parse($report->achieved_at)->format('d M Y') : '-' }}
            </td>

            <td class="center">
                {{ ucfirst($report->status) }}
            </td>

            <td class="right">
    ₹ {{ number_format($report->reward->target_amount ?? 0, 2) }}
</td>

        </tr>

    @empty

        <tr>
            <td colspan="7" class="center">
                No records found.
            </td>
        </tr>

    @endforelse

    </tbody>

<tfoot>

<tr>

    <th colspan="5" class="right">
      Total Records ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
    </th>

    <th colspan="2" class="center">
        Pending Rewards: {{ $reports->where('status','approved')->count() }}
        &nbsp; | &nbsp;
       Delivered Rewards : {{ $reports->where('status','delivered')->count() }}
    </th>

</tr>

</tfoot>

</table>

@endsection