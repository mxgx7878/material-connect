<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #fff; }
        .header { background: #2c3e50; color: #fff; padding: 20px; border-radius: 6px 6px 0 0; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { background: #f8f9fa; padding: 20px; border-radius: 0 0 6px 6px; }
        .meta { background: #fff; padding: 15px; border-radius: 4px; margin: 15px 0; border: 1px solid #e1e4e8; }
        .meta-row { padding: 6px 0; border-bottom: 1px solid #eee; }
        .meta-row:last-child { border-bottom: none; }
        .meta-label { font-weight: 600; color: #666; display: inline-block; width: 160px; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $heading }}</h1>
    </div>
    <div class="body">
        <p>{{ $bodyMessage }}</p>

        <div class="meta">
            <div class="meta-row"><span class="meta-label">Dispute Number</span><span>{{ $dispute->dispute_number }}</span></div>
            <div class="meta-row"><span class="meta-label">Category</span><span>{{ $dispute->category }}</span></div>
            <div class="meta-row"><span class="meta-label">Status</span><span>{{ ucfirst(str_replace('_', ' ', $dispute->status)) }}</span></div>
            @if($dispute->invoice)
                <div class="meta-row"><span class="meta-label">Invoice</span><span>{{ $dispute->invoice->invoice_number }}</span></div>
            @endif
            @if($recipientRole === 'supplier' && $dispute->supplier_response_deadline)
                <div class="meta-row"><span class="meta-label">Response Deadline</span><span>{{ $dispute->supplier_response_deadline->format('M j, Y H:i') }} UTC</span></div>
            @endif
            @if($dispute->resolution_outcome)
                <div class="meta-row"><span class="meta-label">Outcome</span><span>{{ ucfirst(str_replace('_', ' ', $dispute->resolution_outcome)) }}</span></div>
            @endif
        </div>

        <p style="color: #666; font-size: 13px;">Log in to the portal for full details.</p>

        <div class="footer">
            This is an automated notification. Please do not reply.
        </div>
    </div>
</body>
</html>