@php use Illuminate\Support\Str; @endphp

<div x-data="{ activeImage: '{{ $product->image[0] ?? '' }}' }"
    style="display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start;">

    {{-- LEFT: IMAGE SECTION --}}
    <div style="flex:1; min-width:300px;">

        {{-- MAIN IMAGE --}}
        <div style="
            background:#fafafa;
            border:1px solid #eee;
            border-radius:12px;
            padding:10px;
            text-align:center;
            margin-bottom:12px;
        ">
            <template x-if="activeImage.endsWith('.mp4')">
                <video controls style="width:100%; height:260px; object-fit:contain; border-radius:10px;">
                    <source :src="activeImage">
                </video>
            </template>

            <template x-if="!activeImage.endsWith('.mp4')">
                <img :src="activeImage" style="width:100%; height:260px; object-fit:contain; border-radius:10px;" />
            </template>
        </div>

        {{-- THUMBNAILS --}}
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            @foreach($product->image as $img)
                <div @click="activeImage = '{{ $img }}'" style="
                                    cursor:pointer;
                                    border:2px solid transparent;
                                    border-radius:8px;
                                    overflow:hidden;
                                " :style="activeImage === '{{ $img }}' ? 'border:2px solid #111' : ''">
                    @if(Str::endsWith($img, ['.mp4', '.mov']))
                        <video width="60" height="60" style="object-fit:cover;">
                            <source src="{{ $img }}">
                        </video>
                    @else
                        <img src="{{ $img }}" style="width:60px; height:60px; object-fit:cover;" />
                    @endif
                </div>
            @endforeach
        </div>

    </div>

    {{-- RIGHT: DETAILS --}}
    <div style="flex:1; min-width:280px;">

      <h2 style="font-size:20px; font-weight:700; margin-bottom:6px;">
    {{ $product->name }}
</h2>

@if($product->short_description)
    <p style="
        color:#6b7280;
        font-size:14px;
        line-height:1.6;
        margin-bottom:12px;
    ">
        {{ $product->short_description }}
    </p>
@endif

        <p style="color:#666; font-size:13px; margin-bottom:6px;">
            <strong>Brand:</strong> {{ $product->brand }}
        </p>

        <p style="color:#666; font-size:13px; margin-bottom:10px;">
            <strong>Category:</strong> {{ $product->category->name ?? '-' }}
        </p>

        {{-- VARIANT DETAILS --}}
        {{-- VARIANT DETAILS --}}
        @if($variant)

            <div style="margin-top:18px;">

                {{-- TITLE --}}
                <div style="
                        display:flex;
                        justify-content:space-between;
                        align-items:flex-start;
                        margin-bottom:18px;
                        gap:20px;
                    ">

                    <div>



                        <div style="
                        font-size:14px;
                        font-weight:800;
                        color:#111827;
                        line-height:1.2;
                    ">
                            Package Size: {{ $variant->packing_size ?? '-' }}
                        </div>

                        <div style="
                                        font-size:13px;
                                        color:#6b7280;
                                        margin-top:2px;
                                    ">
                            Batch No: {{ $variant->batch_no ?? '-' }}
                        </div>
                        
                        <div style="
    font-size:13px;
    color:#6b7280;
    margin-top:2px;
">
    HSN Code: {{ $variant->hsn_code ?? '-' }}
</div>

                    </div>



                </div>

                {{-- PRICE --}}
                <div style="
                                display:flex;
                                align-items:end;
                                gap:12px;
                                margin-bottom:22px;
                            ">

                    <div>

                        <div style="
                                        font-size:12px;
                                        color:#6b7280;
                                        margin-bottom:4px;
                                    ">
                            Offer Price
                        </div>

                        <div style="
                                        font-size:30px;
                                        font-weight:900;
                                        color:#15803d;
                                        line-height:1;
                                    ">
                            ₹{{ number_format($variant->offer_price ?? 0, 2) }}
                        </div>

                    </div>

                    <div style="
                                    text-decoration:line-through;
                                    color:#9ca3af;
                                    font-size:18px;
                                    margin-bottom:2px;
                                ">
                        ₹{{ number_format($variant->price ?? 0, 2) }}
                    </div>
                    <div style="
                        background:#dcfce7;
                        color:#15803d;
                        padding:6px 12px;
                        border-radius:999px;
                        font-size:14px;
                        font-weight:800;
                        white-space:nowrap;
                        margin-top:6px;
                    ">
                        {{ $variant->discount_percentage ?? 0 }}% OFF
                    </div>
                </div>

                {{-- DETAILS --}}
                <div style="
                                display:grid;
                                grid-template-columns:repeat(2,minmax(0,1fr));
                                gap:14px 28px;
                                font-size:14px;
                            ">

                    <div>
                        <div style="color:#6b7280; font-size:12px;">
                            GST Percentage
                        </div>

                        <div style="font-weight:700;">
                            {{ $variant->gst_percentage ?? 0 }}%
                        </div>
                    </div>

                    <div>
                        <div style="color:#6b7280; font-size:12px;">
                            GST Price
                        </div>

                        <div style="font-weight:700;">
                            ₹{{ number_format($variant->gst_price ?? 0, 2) }}
                        </div>
                    </div>

                    <!-- <div>
                        <div style="color:#6b7280; font-size:12px;">
                            PV
                        </div>

                        <div style="font-weight:700;">
                            {{ $variant->pv ?? 0 }}
                        </div>
                    </div> -->

                    <div>
                        <div style="color:#6b7280; font-size:12px;">
                            BV
                        </div>

                        <div style="font-weight:700;">
                            {{ $variant->bv ?? 0 }}
                        </div>
                    </div>
<div>
    <div style="color:#6b7280; font-size:12px;">
        Cashback
    </div>

    <div style="font-weight:700;">
        {{ number_format($variant->cashback ?? 0, 2) }}
    </div>
</div>
<div>
    <div style="color:#6b7280; font-size:12px;">
        Mega Branch Commission
    </div>

    <div style="font-weight:700;">
        {{ $variant->mega_branch_commission ?? 0 }}%
    </div>
</div>

<div>
    <div style="color:#6b7280; font-size:12px;">
        Mini Branch Commission
    </div>

    <div style="font-weight:700;">
        {{ $variant->mini_branch_commission ?? 0 }}%
    </div>
</div>

<div>
    <div style="color:#6b7280; font-size:12px;">
        Pincode Branch Commission
    </div>

    <div style="font-weight:700;">
        {{ $variant->pincode_branch_commission ?? 0 }}%
    </div>
</div>
                    <div>
                        <div style="color:#6b7280; font-size:12px;">
                            Available Stock
                        </div>

                        <div style="
                                        font-weight:800;
                                        color:#2563eb;
                                    ">
                            {{ $variant->stock ?? 0 }}
                        </div>
                    </div>
                    
                    <div>
    <div style="color:#6b7280; font-size:12px;">
        Minimum Quantity
    </div>

    <div style="font-weight:700;">
        {{ $variant->minimum_quantity ?? 1 }}
    </div>
</div>

                    <div>
                        <div style="color:#6b7280; font-size:12px;">
                            Manufacturing Date
                        </div>

                        <div style="font-weight:700;">
                            {{ $variant->mfc_date ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div style="color:#6b7280; font-size:12px;">
                            Expiry Date
                        </div>

                        <div style="font-weight:700;">
                            {{ $variant->expiry_date ?? '-' }}
                        </div>
                    </div>
                    
                    <div>
    <div style="color:#6b7280; font-size:12px;">
        Product Status
    </div>

    <div style="font-weight:700;">
        {{ ucfirst(str_replace('_', ' ', $variant->product_status ?? 'order_now')) }}
    </div>
</div>

                </div>

            </div>

        @endif

        {{-- DESCRIPTION --}}
        
        <p style="margin-top:14px; font-size:14px; color:#444; line-height:1.6;">
           <strong>Description:</strong>  {{ $product->description }}
        </p>

        {{-- TRENDING --}}
        <p style="margin-top:14px; font-size:13px;">
            <strong>Trending:</strong>
            <span style="{{ $product->is_viral ? 'color:green;' : 'color:#777;' }}">
                {{ $product->is_viral ? 'Yes 🔥' : 'No' }}
            </span>
        </p>

    </div>

</div>