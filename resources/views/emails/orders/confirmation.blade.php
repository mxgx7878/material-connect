@php
    $logo = 'https://demowebportals.com/material_connect/public/assets/img/logo-text.png';
    $reqDate = $order->delivery_date
        ? \Carbon\Carbon::parse($order->delivery_date)->format('D, d M Y')
        : 'To be scheduled';
@endphp
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
        <tr><td style="padding:24px 32px;border-bottom:1px solid #eef0f3;">
          <img src="{{ $logo }}" alt="Material Connect" height="34" style="display:block;">
        </td></tr>
        <tr><td style="padding:28px 32px 8px;">
          <h1 style="margin:0 0 6px;font-size:20px;">We've received your order</h1>
          <p style="margin:0;color:#4b5563;font-size:14px;line-height:22px;">
            Hi {{ $clientName }}, thanks for your order. Our team is reviewing it now — you'll get a separate email with pricing and your invoice once it's confirmed.
          </p>
        </td></tr>
        <tr><td style="padding:16px 32px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border:1px solid #eef0f3;border-radius:10px;">
            <tr>
              <td style="padding:14px 16px;font-size:13px;color:#6b7280;">Order</td>
              <td style="padding:14px 16px;font-size:13px;font-weight:bold;text-align:right;">{{ $orderRef }}</td>
            </tr>
            <tr>
              <td style="padding:14px 16px;font-size:13px;color:#6b7280;border-top:1px solid #eef0f3;">Status</td>
              <td style="padding:14px 16px;font-size:13px;font-weight:bold;text-align:right;border-top:1px solid #eef0f3;">{{ $order->order_status ?? 'Received' }}</td>
            </tr>
            <tr>
              <td style="padding:14px 16px;font-size:13px;color:#6b7280;border-top:1px solid #eef0f3;">Requested delivery</td>
              <td style="padding:14px 16px;font-size:13px;font-weight:bold;text-align:right;border-top:1px solid #eef0f3;">{{ $reqDate }}</td>
            </tr>
            @if($order->delivery_address)
            <tr>
              <td style="padding:14px 16px;font-size:13px;color:#6b7280;border-top:1px solid #eef0f3;">Delivery address</td>
              <td style="padding:14px 16px;font-size:13px;font-weight:bold;text-align:right;border-top:1px solid #eef0f3;">{{ $order->delivery_address }}</td>
            </tr>
            @endif
          </table>
        </td></tr>
        <tr><td style="padding:8px 32px 4px;">
          <p style="margin:0 0 8px;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af;font-weight:bold;">Items</p>
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
            <tr style="color:#6b7280;">
              <td style="padding:8px 0;border-bottom:1px solid #eef0f3;">Product</td>
              <td style="padding:8px 0;border-bottom:1px solid #eef0f3;text-align:right;">Qty</td>
            </tr>
            @foreach($order->items as $item)
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">{{ $item->product->product_name ?? 'Item' }}</td>
              <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;text-align:right;">
                {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} {{ $item->product->unit_of_measure ?? '' }}
              </td>
            </tr>
            @endforeach
          </table>
        </td></tr>
        <tr><td style="padding:24px 32px 8px;">
          <a href="{{ $portalUrl }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 22px;border-radius:8px;">View your order</a>
        </td></tr>
        <tr><td style="padding:16px 32px 28px;">
          <p style="margin:0;color:#9ca3af;font-size:12px;line-height:18px;">
            Material Connect Pty Ltd · support@materialconnect.com.au<br>
            You're receiving this because an order was placed under your account.
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>