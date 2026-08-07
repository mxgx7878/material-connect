<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f6fa; font-family:Arial, Helvetica, sans-serif; color:#172033;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
        {{ $preheader }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#f3f6fa;">
        <tr>
            <td align="center" style="padding:36px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:620px; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 28px rgba(23,32,51,.08);">
                    <tr>
                        <td style="height:6px; background-color:{{ $brandColor }}; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:28px 38px 20px; border-bottom:1px solid #edf0f5;">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" style="display:block; max-width:180px; max-height:54px; border:0;">
                            @else
                                <div style="font-size:22px; font-weight:700; color:#172033; letter-spacing:-.3px;">
                                    {{ config('app.name') }}
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:36px 38px 10px;">
                            <div style="display:inline-block; padding:7px 11px; border-radius:20px; background:#eef4ff; color:{{ $brandColor }}; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">
                                {{ $badge }}
                            </div>

                            <h1 style="margin:18px 0 12px; font-size:28px; line-height:1.25; color:#172033; letter-spacing:-.5px;">
                                {{ $title }}
                            </h1>

                            <p style="margin:0 0 14px; font-size:16px; line-height:1.7; color:#566176;">
                                Hello {{ $recipientName }},
                            </p>

                            <p style="margin:0; font-size:16px; line-height:1.7; color:#566176;">
                                {{ $bodyText }}
                            </p>
                        </td>
                    </tr>

                    @if(!empty($details))
                        <tr>
                            <td style="padding:22px 38px 6px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background:#f8fafc; border:1px solid #e7ebf1; border-radius:10px;">
                                    @foreach($details as $label => $value)
                                        <tr>
                                            <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#657086; border-bottom:{{ $loop->last ? '0' : '1px solid #e7ebf1' }}; width:35%;">
                                                {{ $label }}
                                            </td>
                                            <td style="padding:12px 16px; font-size:14px; color:#172033; border-bottom:{{ $loop->last ? '0' : '1px solid #e7ebf1' }};">
                                                {{ $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if(!empty($actionUrl) && !empty($actionText))
                        <tr>
                            <td style="padding:26px 38px 10px;">
                                <a href="{{ $actionUrl }}" style="display:inline-block; padding:14px 24px; background-color:{{ $accentColor }}; color:#ffffff; text-decoration:none; border-radius:8px; font-size:15px; font-weight:700;">
                                    {{ $actionText }}
                                </a>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:24px 38px 38px;">
                            <p style="margin:0; font-size:14px; line-height:1.7; color:#788398;">
                                Need help? Email <a href="mailto:{{ $supportAddress }}" style="color:{{ $brandColor }}; text-decoration:none;">{{ $supportAddress }}</a>
                                or call <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}" style="color:{{ $brandColor }}; text-decoration:none;">{{ $supportPhone }}</a>.
                            </p>
                            <p style="margin:18px 0 0; font-size:14px; line-height:1.7; color:#566176;">
                                Kind regards,<br>
                                <strong style="color:#172033;">The {{ config('app.name') }} Team</strong>
                            </p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:620px;">
                    <tr>
                        <td align="center" style="padding:20px 20px 0; font-size:12px; line-height:1.6; color:#929bad;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated account notification.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>