@php
    $logo   = 'https://demowebportals.com/material_connect/public/assets/img/logo-text.png';
    $issued = $invoice->issued_date ? \Carbon\Carbon::parse($invoice->issued_date)->format('D, d M Y') : now()->format('D, d M Y');
    $due    = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('D, d M Y') : '—';
    $total  = number_format((float) ($invoice->total_amount ?? 0), 2);
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
          <h1 style="margin:0 0 6px;font-size:20px;">Your invoice is ready</h1>
          <p style="margin:0;color:#4b5563;font-size:14px;line-height:22px;">
            Invoice <strong>{{ $invoice->invoice_number }}</strong> for your order is available to view and pay in your portal.
          </p>
        </td></tr>
        <tr><td style="padding:16px 32px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border:1px solid #eef0f3;border-radius:10px;">
            <tr>
              <td style="padding:14px 16px;font-size:13px;color:#6b7280;">Issued</td>
              <td style="padding:14px 16px;font-size:13px;font-weight:bold;text-align:right;">{{ $issued }}</td>
            </tr>
            <tr>
              <td style="padding:14px 16px;font-size:13px;color:#6b7280;border-top:1px solid #eef0f3;">Due</td>
              <td style="padding:14px 16px;font-size:13px;font-weight:bold;text-align:right;border-top:1px solid #eef0f3;">{{ $due }}</td>
            </tr>
            <tr>
              <td style="padding:14px 16px;font-size:14px;color:#111827;border-top:1px solid #eef0f3;font-weight:bold;">Total (inc. GST)</td>
              <td style="padding:14px 16px;font-size:14px;font-weight:bold;text-align:right;border-top:1px solid #eef0f3;">${{ $total }}</td>
            </tr>
          </table>
        </td></tr>
        @if($invoice->items && $invoice->items->count())
        <tr><td style="padding:8px 32px 4px;">
          <p style="margin:0 0 8px;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af;font-weight:bold;">Line items</p>
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
            <tr style="color:#6b7280;">
              <td style="padding:8px 0;border-bottom:1px solid #eef0f3;">Product</td>
              <td style="padding:8px 0;border-bottom:1px solid #eef0f3;text-align:right;">Qty</td>
              <td style="padding:8px 0;border-bottom:1px solid #eef0f3;text-align:right;">Amount</td>
            </tr>
            @foreach($invoice->items as $line)
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">{{ $line->product_name ?? 'Item' }}</td>
              <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;text-align:right;">{{ rtrim(rtrim(number_format((float) $line->quantity, 2), '0'), '.') }}</td>
              <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;text-align:right;">${{ number_format((float) $line->line_total, 2) }}</td>
            </tr>
            @endforeach
          </table>
        </td></tr>
        @endif
        <tr><td style="padding:24px 32px 8px;">
          <a href="{{ $portalUrl }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 22px;border-radius:8px;">View &amp; pay invoice</a>
        </td></tr>
        <tr><td style="padding:16px 32px 28px;">
          <p style="margin:0;color:#9ca3af;font-size:12px;line-height:18px;">
            Payment must be received in full prior to delivery unless otherwise agreed in writing.<br>
            Material Connect Pty Ltd · support@materialconnect.com.au
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>