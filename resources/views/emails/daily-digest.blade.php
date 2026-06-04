<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Summary</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 24px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 20px; margin: 0 0 4px; color: #1a1a1a; }
        .subtitle { color: #666; font-size: 14px; margin: 0 0 20px; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: #999; margin: 0 0 12px; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .stat { text-align: center; padding: 16px; background: #f9f9f9; border-radius: 6px; }
        .stat-value { font-size: 28px; font-weight: 700; color: #2563eb; }
        .stat-label { font-size: 12px; color: #666; margin-top: 4px; }
        .meeting-item { display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee; }
        .meeting-item:last-child { border-bottom: none; }
        .meeting-time { font-size: 12px; color: #666; min-width: 60px; }
        .meeting-title { flex: 1; font-size: 14px; color: #1a1a1a; }
        .meeting-user { font-size: 12px; color: #999; }
        .btn { display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 24px; }
        .empty { color: #999; font-size: 14px; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Hey {{ $data['name'] }}!</h1>
            <p class="subtitle">Here's your summary for {{ $data['date'] }}</p>

            <div class="stat-grid">
                <div class="stat">
                    <div class="stat-value">{{ $data['new_leads'] }}</div>
                    <div class="stat-label">New Leads</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $data['new_clients'] }}</div>
                    <div class="stat-label">New Clients</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $data['open_tasks'] }}</div>
                    <div class="stat-label">Open Tasks</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $data['open_tickets'] }}</div>
                    <div class="stat-label">Open Tickets</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Today's Meetings</h2>
            @forelse($data['meetings'] as $meeting)
                <div class="meeting-item">
                    <span class="meeting-time">{{ $meeting['time'] }}</span>
                    <span class="meeting-title">{{ $meeting['title'] }}</span>
                    <span class="meeting-user">{{ $meeting['user'] }}</span>
                </div>
            @empty
                <p class="empty">No meetings scheduled today.</p>
            @endforelse
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/dashboard') }}" class="btn">Open Dashboard</a>
        </div>

        <div class="footer">
            <p>PlazaOS — Your daily business summary</p>
            <p>
                <a href="{{ url('/settings/notifications') }}" style="color: #999;">Change notification settings</a>
            </p>
        </div>
    </div>
</body>
</html>
