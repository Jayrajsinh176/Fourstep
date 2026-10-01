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
    style="font-family:'Segoe UI',sans-serif;background:#f3f4f6;padding:25px;">

    <div
        style="max-width:850px;margin:auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);">

        {{-- HEADER --}}
        <div
            style="
                background:linear-gradient(135deg,#12376d,#1d4b8f);
                padding:16px 22px;
                color:white;
                border-bottom:4px solid #0f2d5c;
            ">

            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:16px;
                ">

                {{-- LEFT: logo + name + address --}}
                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:14px;
                        flex:1;
                        min-width:0;
                    ">

                    {{-- LOGO --}}
                    <div>
                        <img
                            src="{{ asset('images/fourstep_logo.png') }}"
                            style="
                                width:48px;
                                height:48px;
                                background:white;
                                border-radius:10px;
                                padding:5px;
                                object-fit:contain;
                                flex-shrink:0;
                            ">
                    </div>

                    {{-- COMPANY NAME + ADDRESS --}}
                    <div style="min-width:0;">

                        <div
                            style="
                                font-size:18px;
                                font-weight:800;
                                letter-spacing:0.4px;
                                line-height:1;
                                margin-bottom:6px;
                            ">
                            4STEP RETAIL
                        </div>

                        <div
                            style="
                                font-size:11.5px;
                                color:#bfcde0;
                                line-height:1.7;
                            ">
                            1st Floor, Shop No.24, Divya Plaza, Nr. Kamlanager Lake, Ajwa Road, Vadodara-Gujarat - 390019
                        </div>

                        <div
                            style="
                                display:flex;
                                flex-wrap:wrap;
                                gap:0 14px;
                                font-size:11.5px;
                                color:#bfcde0;
                                margin-top:2px;
                                line-height:1.7;
                            ">
                            <span>4stepretail@gmail.com</span>
                            <span style="border-left:1px solid #3d5a80;padding-left:14px;">support@fourstepretail.com</span>
                            <span style="border-left:1px solid #3d5a80;padding-left:14px;">+91 97262 86000</span>
                        </div>

                    </div>

                </div>

                {{-- RIGHT: Print Button --}}
                <div style="flex-shrink:0;">
                    <button
                        onclick="window.print()"
                        style="
                            background:white;
                            color:#12376d;
                            border:none;
                            padding:8px 14px;
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

        </div>

        {{-- BUYER DETAILS --}}
        <div style="padding:30px;">

            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    align-items:flex-start;
                    gap:20px;
                ">

                <div style="flex:1;">

                    <div
                        style="
                            font-size:26px;
                            font-weight:700;
                            color:#1f2937;
                            margin-bottom:15px;
                        ">

                        {{ $order->delivery_name ?? 'Customer' }}

                    </div>

                    <div
                        style="
                            font-size:14px;
                            color:#4b5563;
                            line-height:2;
                        ">

                        <strong>Buyer Address :</strong>
                        {{ $order->delivery_address ?? '-' }}
                        <br>

                        <strong>Mobile No :</strong>
                        {{ $order->member->mobile_no ?? '-' }}
                        <br>

                        <strong>Email ID :</strong>
                        {{ $order->member->email ?? '-' }}
                        <br>

                        @if($order->order_type)
                        <strong>MLM Order Type :</strong>
                        {{ ucfirst($order->order_type) }}
                        @endif

                    </div>

                </div>

                <div
                    style="
                        background:#eff6ff;
                        padding:18px;
                        border-radius:14px;
                        min-width:240px;
                    ">

                    <div
                        style="
                            font-size:12px;
                            color:#6b7280;
                            margin-bottom:6px;
                        ">
                        Invoice Number
                    </div>

                    <div
                        style="
                            font-size:22px;
                            font-weight:700;
                            color:#1e3a8a;
                        ">
                        {{ $order->invoice_id }}
                    </div>

                    <div
                        style="
                            margin-top:15px;
                            font-size:13px;
                            color:#374151;
                            line-height:1.8;
                        ">

                        <strong>Payment Mode :</strong>
                        {{ strtoupper($order->payment_method) }}
                        <br>

                        <strong>Delivery Type :</strong>
                        {{ ucfirst($order->delivery_type ?? '-') }}
                        <br>

                        <strong>Status :</strong>
                        {{ ucfirst($order->status ?? '-') }}
                        <br>

                        <strong>Order Date :</strong>
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}

                    </div>

                </div>

            </div>

        </div>

        {{-- PRODUCTS --}}
        <div style="padding:0 30px 30px;">

            <div
                style="
                    font-size:18px;
                    font-weight:700;
                    margin-bottom:18px;
                    color:#0f172a;
                ">
                PRODUCTS
            </div>

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

    // SAFE VARIANT FETCH
    $variant = \App\Models\ProductVariantecom::where(
        'product_id',
        $product->id
    )->first();

    $qty = $item->quantity ?? 1;

    // PRODUCT DETAILS
    $brandName = $product->brand ?? '-';

    $productName = $product->name ?? '-';

    // VARIANT DETAILS
    $packingSize = $variant->packing_size ?? '-';

    $mrp = $variant->price ?? 0;
$pv = $variant->pv ?? 0;

$bv = $variant->bv ?? 0;
    $gstPercent = $variant->gst_percentage ?? 0;

    $offerPrice = $variant->offer_price ?? $mrp;

    // WITHOUT GST
    if ($gstPercent > 0) {

        $withoutGstPrice =
            $offerPrice / (1 + ($gstPercent / 100));

    } else {

        $withoutGstPrice = $offerPrice;
    }

    // GST
    $gstAmount =
        $offerPrice - $withoutGstPrice;

    // LINE TOTAL
    $lineTotal = $offerPrice * $qty;

    // TOTALS
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

            {{-- TOTALS --}}
            <div
                style="
                    display:flex;
                    justify-content:flex-end;
                    margin-top:25px;
                ">

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