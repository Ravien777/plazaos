<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Status Update</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 24px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 20px; margin: 0 0 4px; color: #1a1a1a; }
        .subtitle { color: #666; font-size: 14px; margin: 0 0 20px; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 12px; }
        .personal-note { background: #f0fdf4; border-left: 4px solid #22c55e; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; font-style: italic; color: #166534; }
        .section { margin-bottom: 20px; }
        .section:last-child { margin-bottom: 0; }
        .section-title { font-size: 16px; font-weight: 600; margin: 0 0 8px; color: #1a1a1a; }
        .section-list { margin: 0; padding: 0; list-style: none; }
        .section-list li { padding: 6px 0; font-size: 14px; color: #333; border-bottom: 1px solid #f0f0f0; }
        .section-list li:last-child { border-bottom: none; }
        .empty { color: #999; font-size: 14px; font-style: italic; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Hey {{ $data['client_name'] }}!</h1>
            <p class="subtitle">Here's the weekly update for <strong>{{ $data['project_name'] }}</strong></p>

            @if ($data['personal_note'])
                <div class="personal-note">
                    {{ $data['personal_note'] }}
                </div>
            @endif

            <div class="section">
                <div class="section-title" style="color: #16a34a;">&#10003; Completed This Week</div>
                @if (count($data['completed']) > 0)
                    <ul class="section-list">
                        @foreach ($data['completed'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="empty">Nothing completed yet this week.</p>
                @endif
            </div>

            <div class="section">
                <div class="section-title" style="color: #d97706;">&#9881; In Progress</div>
                @if (count($data['in_progress']) > 0)
                    <ul class="section-list">
                        @foreach ($data['in_progress'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="empty">No active tasks.</p>
                @endif
            </div>

            <div class="section">
                <div class="section-title" style="color: #6366f1;">&#128196; Recent Activity</div>
                @if (count($data['recent_activity']) > 0)
                    <ul class="section-list">
                        @foreach ($data['recent_activity'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="empty">No recent activity.</p>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Sent via PlazaOS — Your Business Platform</p>
        </div>
    </div>
</body>
</html>
