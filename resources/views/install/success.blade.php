@extends('install.layout')

@section('content')
<div class="installer-body" style="text-align: center; padding: 3rem 2.5rem;">
    <div style="width: 64px; height: 64px; background: rgba(16, 185, 129, 0.15); border: 2px solid rgba(16, 185, 129, 0.4); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; box-shadow: 0 0 30px rgba(16, 185, 129, 0.3);">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </div>

    <h1 class="step-title" style="font-size: 1.85rem; margin-bottom: 0.5rem;">Installation Complete!</h1>
    <p class="step-desc" style="max-width: 480px; margin: 0 auto 2rem auto;">
        Corevisys License Server is now fully initialized. Database tables have been migrated, cryptographic signing keys generated, and administrator account provisioned.
    </p>

    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-card); border-radius: 12px; padding: 1.25rem; text-align: left; margin-bottom: 2rem;">
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
            <strong style="color: #cbd5e1;">Administrator:</strong> {{ $adminEmail ?? 'Configured' }}
        </div>
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
            <strong style="color: #cbd5e1;">Security Status:</strong>
            <span style="color: #34d399; font-weight: 600;">Locked</span> (Web setup wizard permanently disabled)
        </div>
        <div style="font-size: 0.82rem; color: #94a3b8; border-top: 1px solid rgba(255, 255, 255, 0.06); padding-top: 0.5rem; margin-top: 0.5rem;">
            💡 <strong>Next Step:</strong> Ensure cPanel / server Cron Job is active for license expiration and pulses:
            <code style="display: block; background: rgba(0,0,0,0.4); padding: 0.4rem 0.6rem; border-radius: 6px; font-size: 0.78rem; color: #38bdf8; margin-top: 0.35rem;">* * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1</code>
        </div>
    </div>

    <a href="{{ url('/login') }}" class="btn btn-primary btn-block" style="padding: 1rem; font-size: 1.05rem;">
        <span>Log in to Admin Dashboard</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
</div>
@endsection
