<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corevisys License System - cPanel Setup Wizard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #090d16;
            --card-bg: #111827;
            --border: #1f2937;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            max-width: 680px;
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.5);
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 20px;
            font-weight: 700;
            color: #60a5fa;
            margin-bottom: 8px;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
        }
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }
        .section-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin: 20px 0 10px;
            font-weight: 600;
        }
        .check-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            font-size: 14px;
        }
        .badge {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
        }
        .badge-success { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .badge-danger { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
            width: 100%;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        .btn-secondary {
            background: #1f2937;
            color: #e5e7eb;
            margin-top: 10px;
        }
        .btn-secondary:hover {
            background: #374151;
        }
        .log-box {
            background: #000;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #a7f3d0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Corevisys Systems
            </div>
            <h1 class="title">cPanel Deployment Wizard</h1>
            <p class="subtitle">Quick 1-Click Database Setup & System Initializer</p>
        </div>

        @if(session('setup_success'))
            <div class="alert alert-success">
                <strong>Setup Completed Successfully!</strong>
                <p style="margin-top: 4px;">Your database is migrated and ready to use.</p>
            </div>
            <div class="log-box">
                @foreach(session('setup_success') as $line)
                    <div>{{ $line }}</div>
                @endforeach
            </div>
            <a href="{{ url('/') }}" class="btn btn-primary">Go to Home Page</a>
            <a href="{{ url('/login') }}" class="btn btn-secondary">Go to Admin Login (admin@corevisys.com / admin123456)</a>
        @elseif(session('error'))
            <div class="alert alert-danger">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if($isInstalled && !session('setup_success'))
            <div class="alert alert-info">
                <strong>System is already installed!</strong>
                <p style="margin-top: 4px;">The database is connected and operational. If you want to re-run setup, remove <code>storage/installed.lock</code> via cPanel File Manager.</p>
            </div>
            <a href="{{ url('/') }}" class="btn btn-primary">Go to Home Page</a>
            <a href="{{ url('/login') }}" class="btn btn-secondary">Login to Dashboard</a>
        @elseif(!session('setup_success'))

            <div class="section-title">Server Environment Checks</div>
            <ul class="check-list">
                @foreach($checks as $check)
                    <li class="check-item">
                        <span>{{ $check['name'] }}</span>
                        <span class="badge {{ $check['status'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $check['current'] }}
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="section-title">Database Connection (.env)</div>
            <ul class="check-list">
                <li class="check-item">
                    <span>Database: <strong>{{ $dbConfig['database'] ?? 'Not Set' }}</strong> (User: {{ $dbConfig['username'] ?? 'Not Set' }})</span>
                    <span class="badge {{ $dbConnected ? 'badge-success' : 'badge-danger' }}">
                        {{ $dbConnected ? 'Connected' : 'Connection Failed' }}
                    </span>
                </li>
                @if($dbConnected)
                    <li class="check-item">
                        <span>Tables in Database</span>
                        <span class="badge {{ $tablesCount > 0 ? 'badge-success' : 'badge-danger' }}">
                            {{ $tablesCount }} Tables Found
                        </span>
                    </li>
                @else
                    <li class="check-item" style="color: #f87171; font-size: 12px; display: block;">
                        <strong>Connection Error:</strong> {{ $dbError }}<br>
                        <span style="color: var(--text-muted)">Please check DB_HOST, DB_DATABASE, DB_USERNAME, and DB_PASSWORD in your .env file.</span>
                    </li>
                @endif
            </ul>

            @if($dbConnected)
                <form action="{{ route('cpanel.setup.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        {{ $tablesCount > 0 ? 'Re-Migrate & Optimize System' : 'Initialize Database & Setup Now' }}
                    </button>
                </form>
            @else
                <button class="btn btn-secondary" disabled style="opacity: 0.6; cursor: not-allowed;">
                    Fix .env Database Credentials First
                </button>
            @endif

        @endif
    </div>
</body>
</html>
