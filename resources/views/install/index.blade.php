@extends('install.layout')

@section('content')
<div class="installer-body">
    <div class="step-header">
        <h1 class="step-title">Setup & Configuration</h1>
        <p class="step-desc">Provide your database and administrator credentials. Cryptographic keys and settings will be provisioned automatically.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <strong>Please resolve the following issues:</strong>
                <ul style="margin-left: 1.25rem; margin-top: 0.35rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Server Checklist Collapsible -->
    <details style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.02); border: 1px solid var(--border-card); border-radius: 10px; padding: 0.75rem 1rem;" {{ $allSatisfied ? '' : 'open' }}>
        <summary style="cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #cbd5e1; display: flex; align-items: center; justify-content: space-between;">
            <span>System Requirements Check</span>
            <span class="status-badge {{ $allSatisfied ? 'status-pass' : 'status-fail' }}">
                {{ $allSatisfied ? 'All Checks Passed' : 'Action Required' }}
            </span>
        </summary>
        <div class="req-list" style="margin-top: 1rem;">
            @foreach ($requirements as $key => $req)
                <div class="req-item">
                    <span>{{ $req['name'] }}</span>
                    <span class="status-badge {{ $req['satisfied'] ? 'status-pass' : 'status-fail' }}">
                        {{ $req['satisfied'] ? 'OK' : 'Missing' }}
                    </span>
                </div>
            @endforeach
        </div>
    </details>

    <form method="POST" action="{{ route('install.process') }}" id="installForm">
        @csrf

        <!-- 1. Application Details -->
        <div class="form-section">
            <div class="section-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                1. General Settings
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="app_name">Application Name</label>
                    <input type="text" id="app_name" name="app_name" value="{{ old('app_name', 'Corevisys License') }}" required>
                </div>
                <div class="form-group">
                    <label for="app_url">Application URL</label>
                    <input type="url" id="app_url" name="app_url" value="{{ old('app_url', $suggestedUrl) }}" required placeholder="https://license.corevisys.com">
                    <span class="field-hint">Public root URL without trailing slash</span>
                </div>
            </div>
        </div>

        <!-- 2. Database Details -->
        <div class="form-section">
            <div class="section-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                2. Database Configuration (MySQL / MariaDB)
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>
                </div>
                <div class="form-group">
                    <label for="db_port">Port</label>
                    <input type="number" id="db_port" name="db_port" value="{{ old('db_port', 3306) }}" required>
                </div>
                <div class="form-group">
                    <label for="db_database">Database Name</label>
                    <input type="text" id="db_database" name="db_database" value="{{ old('db_database') }}" required placeholder="cpaneluser_license">
                </div>
                <div class="form-group">
                    <label for="db_username">Database Username</label>
                    <input type="text" id="db_username" name="db_username" value="{{ old('db_username') }}" required placeholder="cpaneluser_dbuser">
                </div>
                <div class="form-group full-width">
                    <label for="db_password">Database Password</label>
                    <input type="password" id="db_password" name="db_password" value="{{ old('db_password') }}" placeholder="Enter strong database password">
                </div>
            </div>

            <!-- Test Connection Button -->
            <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.25rem;">
                <button type="button" id="btnTestDb" class="btn btn-secondary" style="padding: 0.55rem 1.1rem; font-size: 0.85rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Test Connection
                </button>
                <div id="testDbStatus" style="font-size: 0.85rem; font-weight: 500;"></div>
            </div>
        </div>

        <!-- 3. Administrator Details -->
        <div class="form-section">
            <div class="section-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                3. Administrator Account
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="admin_name">Admin Name</label>
                    <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name', 'Administrator') }}" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin@corevisys.com">
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="8" placeholder="Minimum 8 characters">
                </div>
                <div class="form-group">
                    <label for="admin_password_confirmation">Confirm Password</label>
                    <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" required minlength="8" placeholder="Repeat password">
                </div>
            </div>
        </div>

        <button type="submit" id="btnSubmit" class="btn btn-primary btn-block" style="padding: 1rem; font-size: 1.05rem;">
            <span>Complete Installation & Initialize Server</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnTestDb = document.getElementById('btnTestDb');
    const testDbStatus = document.getElementById('testDbStatus');
    const installForm = document.getElementById('installForm');
    const btnSubmit = document.getElementById('btnSubmit');

    btnTestDb.addEventListener('click', async function () {
        const host = document.getElementById('db_host').value.trim();
        const port = document.getElementById('db_port').value.trim();
        const database = document.getElementById('db_database').value.trim();
        const username = document.getElementById('db_username').value.trim();
        const password = document.getElementById('db_password').value;

        if (!host || !database || !username) {
            testDbStatus.innerHTML = '<span style="color: var(--error);">Please enter host, database name and username first.</span>';
            return;
        }

        testDbStatus.innerHTML = '<span style="color: var(--primary);">Connecting...</span>';
        btnTestDb.disabled = true;

        try {
            const token = document.querySelector('input[name="_token"]').value;
            const res = await fetch("{{ route('install.testDb') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    db_host: host,
                    db_port: port,
                    db_database: database,
                    db_username: username,
                    db_password: password
                })
            });

            const data = await res.json();
            if (data.success) {
                testDbStatus.innerHTML = `<span style="color: var(--success); font-weight: 600;">✓ ${data.message}</span>`;
            } else {
                testDbStatus.innerHTML = `<span style="color: var(--error);">✗ ${data.message}</span>`;
            }
        } catch (err) {
            testDbStatus.innerHTML = `<span style="color: var(--error);">✗ Request failed: ${err.message}</span>`;
        } finally {
            btnTestDb.disabled = false;
        }
    });

    installForm.addEventListener('submit', function () {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span>Installing CoreVisys (Migrating & Provisioning)...</span>';
    });
});
</script>
@endsection
