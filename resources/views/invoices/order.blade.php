<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: "Noto Sans Bengali";
            font-style: normal;
            font-weight: normal;
            src: url("{{ resource_path('fonts/NotoSansBengali-Regular.ttf') }}") format("truetype");
        }
        @page { margin: 34px 38px; }
        body { color: #25211e; font-family: "Noto Sans Bengali", DejaVu Sans, sans-serif; font-size: 10px; }
        .top { border-bottom: 2px solid #7b1e2e; padding-bottom: 16px; text-align: center; }
        .company-logo { max-width: 160px; max-height: 70px; margin-bottom: 8px; }
        .brand { color: #7b1e2e; font-size: 21px; font-weight: bold; }
        .muted { color: #766e67; }
        .invoice-title { float: right; color: #7b1e2e; font-size: 18px; font-weight: bold; text-align: right; }
        .clear { clear: both; }
        .meta { width: 100%; margin-top: 20px; }
        .meta td { width: 50%; vertical-align: top; }
        h2 { color: #342d28; font-size: 11px; margin: 0 0 8px; }
        p { line-height: 1.7; margin: 3px 0; }
        table.items { border-collapse: collapse; margin-top: 22px; width: 100%; }
        .items th { background: #f4efeb; color: #4d423b; font-size: 9px; padding: 9px 7px; text-align: left; }
        .items td { border-bottom: 1px solid #e9e2dc; padding: 9px 7px; vertical-align: top; }
        .right { text-align: right !important; }
        .totals { margin: 15px 0 0 auto; width: 230px; }
        .totals td { padding: 5px 0; }
        .grand td { border-top: 1px solid #d8cec6; color: #7b1e2e; font-size: 12px; font-weight: bold; padding-top: 9px; }
        .footer { border-top: 1px solid #e9e2dc; color: #766e67; margin-top: 28px; padding-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="top">
        @if($logo)<img class="company-logo" src="{{ $logo }}" alt="Company logo">@endif
        <div class="brand">{{ $settings['company_name'] ?? 'Store' }}</div>
        @if(!empty($settings['address']))<p>{{ $settings['address'] }}</p>@endif
        <p>{{ collect([$settings['phone_one'] ?? null, $settings['phone_two'] ?? null])->filter()->implode(' | ') }}</p>
        <p>{{ collect([$settings['email_one'] ?? null, $settings['email_two'] ?? null])->filter()->implode(' | ') }}</p>
        <h2 style="margin-top:12px">INVOICE #{{ 10000 + $order->id }}</h2>
        <p class="muted">Order date: {{ $order->created_at->format('M d, Y h:i A') }}</p>
        <div class="clear"></div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <h2>DELIVERY DETAILS</h2>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->phone }}</p>
                @if($order->email)<p>{{ $order->email }}</p>@endif
                <p>{{ $order->address }}</p>
                <p>{{ $order->area === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}</p>
            </td>
            <td>
                <h2>PAYMENT DETAILS</h2>
                <p>Method: {{ $order->payment_method === 'cash_on_delivery' ? 'Cash on delivery' : ucfirst($order->payment_method) }}</p>
                <p>Status: {{ ucfirst($order->payment_status) }}</p>
                <p>Order status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                @if($order->customer_note)<p>Note: {{ $order->customer_note }}</p>@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th>Product</th>
                <th class="right" style="width: 12%">Qty</th>
                <th class="right" style="width: 20%">Unit price</th>
                <th class="right" style="width: 20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}@if($item->variant_name)<br><span class="muted">{{ $item->variant_name }}</span>@endif</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">BDT {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">BDT {{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">BDT {{ number_format((float) $order->subtotal, 2) }}</td></tr>
        @if($order->coupon_code)<tr><td>Coupon ({{ $order->coupon_code }})</td><td class="right">- BDT {{ number_format((float)$order->coupon_discount, 2) }}</td></tr>@endif
        @if($order->gift_card_code)<tr><td>Gift card ({{ $order->gift_card_code }})</td><td class="right">- BDT {{ number_format((float)$order->gift_card_discount, 2) }}</td></tr>@endif
        @if((float)$order->discount_amount > 0 && !$order->coupon_code && !$order->gift_card_code)<tr><td>Discount</td><td class="right">- BDT {{ number_format((float)$order->discount_amount, 2) }}</td></tr>@endif
        <tr><td>Delivery</td><td class="right">BDT {{ number_format((float) $order->delivery_fee, 2) }}</td></tr>
        <tr class="grand"><td>Total payable</td><td class="right">BDT {{ number_format((float) $order->total, 2) }}</td></tr>
    </table>
    <div class="footer">Thank you for your order. Please keep this invoice for your records.</div>
</body>
</html>
