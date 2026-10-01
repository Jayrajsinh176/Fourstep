<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders PDF</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
    
    <img src="{{ public_path('storage/4steplogo.png') }}" width="90">

    <p style="font-size:12px;">
        Orders Report <br>
        From: {{ $from ?? '-' }} | To: {{ $to ?? '-' }}
    </p>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Customer</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Coupon</th>
            <th>Discount</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach($orders as $order)

        @php
            $isMember = !empty($order->mlm_member_id);
        @endphp

        <tr>
            <td>{{ $order->id }}</td>

      <td>
    @foreach($order->items as $item)
        {{ $item->product->name ?? '-' }}
        @if(!$loop->last)
            <br>
        @endif
    @endforeach
</td>

            {{-- CUSTOMER --}}
          <td>
    {{ $isMember
        ? ($order->mlmMember->fullname ?? '-')
        : ($order->delivery_name ?? '-')
    }}
</td>

            {{-- MOBILE --}}
         <td>
    {{ $isMember
        ? ($order->mlmMember->mobile_no ?? '-')
        : '-'
    }}
</td>

            {{-- ADDRESS --}}
<td>
    {{ $order->ecomMember->address ?? $order->delivery_address ?? '-' }}
</td>

            {{-- COUPON --}}
            <td>{{ $order->coupon_code ?? '-' }}</td>

            {{-- DISCOUNT --}}
            <td>&#8377;{{ number_format($order->coupon_discount ?? 0, 2) }}</td>

            {{-- TOTAL --}}
            <td>&#8377;{{ number_format($order->total_amount, 2) }}</td>

            {{-- STATUS --}}
            <td>{{ ucfirst($order->status) }}</td>

            {{-- DATE --}}
            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i:s') }}</td>
        </tr>

        @endforeach
    </tbody>
</table>

</body>
</html>