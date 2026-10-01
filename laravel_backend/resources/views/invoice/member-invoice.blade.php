<style>
    @media print {

        button,
        .fi-modal-close-btn,
        .fi-modal-footer {
            display: none !important;
        }

        body * {
            visibility: hidden;
        }

        #invoice-print,
        #invoice-print * {
            visibility: visible;
        }

        #invoice-print {
            position: absolute;
            left: 0;
            top: 0;
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 10mm;
            box-sizing: border-box;
        }
    }
</style>

@php

    $items = $order->items;

    $subtotal = 0;
    $gstTotal = 0;

@endphp

<div id="invoice-print"
    style="
        font-family:'Segoe UI',sans-serif;
        background:#f3f4f6;
        padding:25px;
    ">

    <div
        style="
            max-width:850px;
            margin:auto;
            background:#fff;
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
        ">

        {{-- HEADER: COMPANY BRANDING --}}
        <div
            style="
                background:#12376d;
                padding:20px 28px;
                display:flex;
                align-items:center;
                gap:16px;
                border-bottom:4px solid #0f2d5c;
            ">

            <div style="flex-shrink:0;">
                <img
                    src="{{ asset('images/fourstep_logo.png') }}"
                    style="
                        width:52px;
                        height:52px;
                        background:white;
                        border-radius:10px;
                        padding:6px;
                        object-fit:contain;
                        display:block;
                    ">
            </div>

            <div style="min-width:0;">

                <div
                    style="
                        font-size:20px;
                        font-weight:800;
                        color:white;
                        letter-spacing:0.5px;
                        line-height:1;
                        margin-bottom:5px;
                    ">
                    4STEP RETAIL
                </div>

                <div
                    style="
                        font-size:11.5px;
                        color:#93b4d8;
                        line-height:1.6;
                    ">
                    1st Floor, Shop No.24, Divya Plaza, Nr. Kamlanager Lake, Ajwa Road, Vadodara – Gujarat 390019
                </div>

                <div
                    style="
                        display:flex;
                        flex-wrap:wrap;
                        gap:0 14px;
                        font-size:11px;
                        color:#7aa5cc;
                        margin-top:4px;
                    ">
                    <span>4stepretail@gmail.com</span>
                    <span style="border-left:1px solid #3d5a80;padding-left:14px;">support@fourstepretail.com</span>
                    <span style="border-left:1px solid #3d5a80;padding-left:14px;">+91 97262 86000</span>
                </div>

            </div>

        </div>

        {{-- INVOICE META BAR --}}
        <div
            style="
                background:#f0f5fc;
                border-bottom:2px solid #d4e2f4;
                padding:0 28px;
                display:flex;
                align-items:center;
                flex-wrap:wrap;
                gap:0;
            ">

            {{-- Invoice No --}}
            <div
                style="
                    padding:14px 24px 14px 0;
                    border-right:1px solid #d4e2f4;
                    margin-right:24px;
                ">
                <div
                    style="
                        font-size:10.5px;
                        font-weight:700;
                        color:#5a7fa8;
                        text-transform:uppercase;
                        letter-spacing:0.6px;
                        margin-bottom:3px;
                    ">
                    Invoice No.
                </div>
                <div
                    style="
                        font-size:22px;
                        font-weight:800;
                        color:#12376d;
                    ">
                    #{{ $order->invoice_id }}
                </div>
            </div>

            {{-- Order Date --}}
            <div
                style="
                    padding:14px 24px 14px 0;
                    border-right:1px solid #d4e2f4;
                    margin-right:24px;
                ">
                <div
                    style="
                        font-size:10.5px;
                        font-weight:700;
                        color:#5a7fa8;
                        text-transform:uppercase;
                        letter-spacing:0.6px;
                        margin-bottom:3px;
                    ">
                    Order Date
                </div>
                <div
                    style="
                        font-size:15px;
                        font-weight:700;
                        color:#0f2d5c;
                    ">
                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                </div>
            </div>

            {{-- Status --}}
            <div style="padding:14px 24px 14px 0;">
                <div
                    style="
                        font-size:10.5px;
                        font-weight:700;
                        color:#5a7fa8;
                        text-transform:uppercase;
                        letter-spacing:0.6px;
                        margin-bottom:3px;
                    ">
                    Status
                </div>
                <span
                    style="
                        display:inline-block;
                        background:#dcfce7;
                        color:#166534;
                        font-size:11.5px;
                        font-weight:700;
                        padding:5px 14px;
                        border-radius:20px;
                    ">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            {{-- Print Button --}}
            <div style="margin-left:auto;padding:14px 0;">
                <button
                    onclick="window.print()"
                    style="
                        background:#12376d;
                        color:white;
                        border:none;
                        padding:9px 16px;
                        border-radius:8px;
                        cursor:pointer;
                        font-size:12px;
                        font-weight:700;
                        white-space:nowrap;
                    ">
                    Print / Save PDF
                </button>
            </div>

        </div>

        {{-- BUYER + DELIVERY --}}
        <div
            style="
                padding:26px;
                display:flex;
                gap:20px;
                flex-wrap:wrap;
            ">

            {{-- BILL TO --}}
            <div
                style="
                    flex:1;
                    min-width:280px;
                    background:#f9fafb;
                    padding:18px;
                    border-radius:14px;
                    border:1px solid #e5e7eb;
                ">

                <div
                    style="
                        font-size:12px;
                        color:#6b7280;
                        margin-bottom:10px;
                        font-weight:700;
                    ">
                    BILL TO
                </div>

                <div
                    style="
                        font-size:18px;
                        font-weight:700;
                        color:#111827;
                        margin-bottom:10px;
                    ">

                    {{ $order->member->fullname ?? '-' }}

                </div>

                <div
                    style="
                        font-size:13px;
                        color:#4b5563;
                        line-height:2;
                    ">

                    <strong>ID :</strong>
                    {{ $order->mlm_member_id ?? '-' }}
                    <br>

                    <strong>Mobile :</strong>
                    {{ $order->member->mobile_no ?? '-' }}
                    <br>

                    <strong>Address :</strong>
                    {{ $order->member->address ?? '-' }}

                </div>

            </div>

            {{-- DELIVERY --}}
            @if($order->delivery_member_id && $order->delivery_member_id != $order->mlm_member_id)

            <div
                style="
                    flex:1;
                    min-width:280px;
                    background:#eff6ff;
                    padding:18px;
                    border-radius:14px;
                    border:1px solid #bfdbfe;
                ">

                <div
                    style="
                        font-size:12px;
                        color:#1d4ed8;
                        margin-bottom:10px;
                        font-weight:700;
                    ">
                    DELIVER TO
                </div>

                <div
                    style="
                        font-size:18px;
                        font-weight:700;
                        color:#111827;
                        margin-bottom:10px;
                    ">

                    {{ $order->delivery_name ?? '-' }}

                </div>

                <div
                    style="
                        font-size:13px;
                        color:#4b5563;
                        line-height:2;
                    ">

                    <strong>ID :</strong>
                    {{ $order->delivery_member_id }}
                    <br>

                    <strong>Address :</strong>
                    {{ $order->delivery_address ?? '-' }}

                </div>

            </div>

            @endif

        </div>

        {{-- PRODUCTS --}}
        <div style="padding:0 26px 26px;">

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    overflow:hidden;
                    border-radius:12px;
                ">

                <thead>

                    <tr
                        style="
                            background:#0f3d75;
                            color:white;
                            font-size:13px;
                        ">

                        <th style="padding:14px;text-align:left;">#</th>

                        <th style="padding:14px;text-align:left;">
                            Brand Name
                        </th>

                        <th style="padding:14px;text-align:left;">
                            Product Name
                        </th>

                        <th style="padding:14px;text-align:center;">
                            Qty
                        </th>

                        <th style="padding:14px;text-align:right;">
                            MRP
                        </th>

                        <th style="padding:14px;text-align:right;">
                            Without GST
                        </th>

                        <th style="padding:14px;text-align:right;">
                            GST %
                        </th>

                        <th style="padding:14px;text-align:right;">
                            PV
                        </th>

                        <th style="padding:14px;text-align:right;">
                            BV
                        </th>

                        <th style="padding:14px;text-align:right;">
                            Final Amount
                        </th>

                    </tr>

                </thead>

                <tbody>

@foreach($items as $index => $item)

@php

    $product = $item->product;

    $variant = \App\Models\ProductVariantecom::where(
        'product_id',
        $product->id
    )->first();

    $qty = $item->quantity ?? 1;

    $brandName = $product->brand ?? '-';

    $productName = $product->name ?? '-';

    $packingSize = $variant->packing_size ?? '-';

    $mrp = $variant->price ?? 0;

    $pv = $variant->pv ?? 0;

    $bv = $variant->bv ?? 0;

    $gstPercent = $variant->gst_percentage ?? 0;

    $offerPrice = $variant->offer_price ?? $mrp;

    if ($gstPercent > 0) {

        $withoutGstPrice =
            $offerPrice / (1 + ($gstPercent / 100));

    } else {

        $withoutGstPrice = $offerPrice;
    }

    $gstAmount =
        $offerPrice - $withoutGstPrice;

    $lineTotal = $offerPrice * $qty;

    $subtotal +=
        ($withoutGstPrice * $qty);

    $gstTotal +=
        ($gstAmount * $qty);

@endphp

<tr
    style="
        border-bottom:1px solid #e5e7eb;
        font-size:14px;
    ">

    <td style="padding:14px;">
        {{ $index + 1 }}
    </td>

    <td style="padding:14px;">
        {{ $brandName }}
    </td>

    <td style="padding:14px;">

        <div style="font-weight:700;">
            {{ $productName }}
        </div>

        <div
            style="
                font-size:12px;
                color:#6b7280;
                margin-top:4px;
            ">

            Packing Size :
            {{ $packingSize }}

        </div>

    </td>

    <td
        style="
            padding:14px;
            text-align:center;
        ">

        {{ $qty }}

    </td>

    <td
        style="
            padding:14px;
            text-align:right;
        ">

        ₹{{ number_format($mrp,2) }}

    </td>

    <td
        style="
            padding:14px;
            text-align:right;
        ">

        ₹{{ number_format($withoutGstPrice,2) }}

    </td>

    <td
        style="
            padding:14px;
            text-align:right;
        ">

        {{ number_format($gstPercent,2) }}%

    </td>

    <td
        style="
            padding:14px;
            text-align:right;
            font-weight:600;
            color:#2563eb;
        ">

        {{ number_format($pv,2) }}

    </td>

    <td
        style="
            padding:14px;
            text-align:right;
            font-weight:600;
            color:#059669;
        ">

        {{ number_format($bv,2) }}

    </td>

    <td
        style="
            padding:14px;
            text-align:right;
            font-weight:700;
        ">

        ₹{{ number_format($lineTotal,2) }}

    </td>

</tr>

@endforeach

                </tbody>

            </table>

            {{-- TOTAL + PAYMENT --}}
            <div
                style="
                    margin-top:24px;
                    display:flex;
                    justify-content:space-between;
                    gap:20px;
                    flex-wrap:wrap;
                ">

                {{-- PAYMENT DETAILS --}}
                <div
                    style="
                        flex:1;
                        min-width:300px;
                        border-radius:14px;
                        overflow:hidden;
                        border:1px solid #dbeafe;
                        background:white;
                    ">

                    <div
                        style="
                            padding:16px 18px;
                            background:#f9fafb;
                            border-bottom:1px solid #e5e7eb;
                            font-weight:700;
                            color:#111827;
                        ">
                        Payment Details
                    </div>

                    <div
                        style="
                            padding:18px;
                            font-size:13px;
                            color:#374151;
                            line-height:2;
                        ">

                        <div style="display:flex;justify-content:space-between;">
                            <span>Payment Method</span>
                            <strong>{{ ucfirst($order->payment_method ?? '-') }}</strong>
                        </div>

                        <div style="display:flex;justify-content:space-between;">
                            <span>Wallet Used</span>
                            <span>
                                {{ $order->wallet_used ? 'Yes' : 'No' }}
                                ({{ $order->wallet_type ?? '-' }})
                            </span>
                        </div>

                        <div style="display:flex;justify-content:space-between;">
                            <span>Wallet Amount</span>
                            <span>
                                ₹{{ number_format($order->wallet_amount ?? 0,2) }}
                            </span>
                        </div>

                        <div style="display:flex;justify-content:space-between;">
                            <span>Coupon</span>
                            <span>{{ $order->coupon_code ?? '-' }}</span>
                        </div>

                        <div style="display:flex;justify-content:space-between;">
                            <span>Discount</span>
                            <span>
                                ₹{{ number_format($order->coupon_discount ?? 0,2) }}
                            </span>
                        </div>

                        <div style="display:flex;justify-content:space-between;">
                            <span>Amount Paid</span>
                            <span>
                                ₹{{ number_format($order->amount_paid ?? 0,2) }}
                            </span>
                        </div>

                    </div>

                </div>

                {{-- TOTALS --}}
                <div
                    style="
                        width:340px;
                        border-radius:14px;
                        overflow:hidden;
                        border:1px solid #dbeafe;
                    ">

                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:14px 18px;
                            background:#f9fafb;
                        ">

                        <span>Sub Total</span>

                        <span>
                            ₹{{ number_format($subtotal,2) }}
                        </span>

                    </div>

                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:14px 18px;
                            background:#fff7ed;
                        ">

                        <span>GST Total</span>

                        <span>
                            ₹{{ number_format($gstTotal,2) }}
                        </span>

                    </div>

                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:18px;
                            background:#0f3d75;
                            color:white;
                            font-size:22px;
                            font-weight:700;
                        ">

                        <span>Grand Total</span>

                        <span>
                            ₹{{ number_format($order->total_amount,2) }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- FOOTER --}}
        <div
            style="
                padding:18px;
                text-align:center;
                font-size:12px;
                color:#9ca3af;
                border-top:1px dashed #d1d5db;
            ">

            Thank you for choosing
            <strong style="color:#0f3d75;">
                4STEP RETAIL
            </strong>

            . This is a system-generated invoice.

        </div>

    </div>

</div>