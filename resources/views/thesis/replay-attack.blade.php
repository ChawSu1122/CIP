@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header">Authentication Security Testing Module</div>
                <div class="card-body">
                    <p class="mb-0">This module demonstrates how compromised authentication credentials are detected and how the system alerts the original user when a second browser attempts unauthorized access.</p>
                </div>
            </div>

            <div class="row gy-4">
                <div class="col-lg-6">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-dark">Token Replay Attack</div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">Browser A is logged in as the victim. When the victim clicks the phishing link, Browser B receives the stolen bearer token and attacker-side data.</p>

                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><strong>Browser A (victim):</strong> {{ $victimName }} is logged in and clicks the phishing link.</li>
                                <li class="list-group-item"><strong>Browser B (attacker):</strong> sees stolen account data after the phishing event.</li>
                                <li class="list-group-item"><strong>Authentication Method:</strong> Token-Based</li>
                                <li class="list-group-item"><strong>Status:</strong> Credential Marked as Compromised</li>
                                <li class="list-group-item"><strong>Source:</strong> Suspicious Link Simulation</li>
                                <li class="list-group-item"><strong>Time:</strong> <span id="token-time">{{ now()->format('Y-m-d H:i:s') }}</span></li>
                            </ul>

                            <div class="mb-3">
                                <button id="token-unauthorized-btn" class="btn btn-danger">Unauthorized Access</button>
                            </div>

                            <div id="token-result" class="alert alert-secondary small" role="status">
                                Attacker Browser B is ready to replay the stolen token and display the victim's account data.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-danger h-100">
                        <div class="card-header bg-danger text-white">Session Hijacking Attack</div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">Browser A is logged in as the victim. When the victim clicks the phishing link, Browser B uses the stolen session cookie to impersonate the victim.</p>

                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><strong>Browser A (victim):</strong> {{ $victimName }} is logged in and clicks the phishing link.</li>
                                <li class="list-group-item"><strong>Browser B (attacker):</strong> sees stolen session data and victim account details.</li>
                                <li class="list-group-item"><strong>Authentication Method:</strong> Session-Based</li>
                                <li class="list-group-item"><strong>Status:</strong> Credential Marked as Compromised</li>
                                <li class="list-group-item"><strong>Source:</strong> Suspicious Link Simulation</li>
                                <li class="list-group-item"><strong>Time:</strong> <span id="session-time">{{ now()->format('Y-m-d H:i:s') }}</span></li>
                            </ul>

                            <div class="mb-3">
                                <button id="session-unauthorized-btn" class="btn btn-danger">Unauthorized Access</button>
                            </div>

                            <div id="session-result" class="alert alert-secondary small" role="status">
                                Attacker Browser B is ready to reuse the session cookie and display the victim's account data.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">Victim Alert Simulation</div>
                <div class="card-body">
                    <p class="small text-muted mb-3">When an unauthorized access attempt is detected, the original user immediately receives a security alert.</p>
                    <div id="victim-status" class="alert alert-info">{{ $victimName }}'s browser is currently active. If unauthorized access is detected, a security alert will appear.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="security-alert-toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto">Security Alert</strong>
            <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <p class="mb-2"><strong>Your account has been accessed from another browser.</strong></p>
            <p class="mb-3">If this wasn't you, please logout immediately.</p>
            <div class="d-flex justify-content-end gap-2">
                <button id="logout-all-btn" type="button" class="btn btn-sm btn-danger">Logout All Devices</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="toast">Dismiss</button>
            </div>
        </div>
    </div>
</div>

<script>
    const victimName = @json($victimName);
    const victimEmail = @json($victimEmail);
    const tokenTime = document.getElementById('token-time');
    const sessionTime = document.getElementById('session-time');
    const tokenResult = document.getElementById('token-result');
    const sessionResult = document.getElementById('session-result');
    const victimStatus = document.getElementById('victim-status');
    const toastEl = document.getElementById('security-alert-toast');
    const bootstrapToast = (typeof bootstrap !== 'undefined' ? bootstrap : window.bootstrap);
    const alertToast = bootstrapToast ? new bootstrapToast.Toast(toastEl, { delay: 10000 }) : null;

    function updateTimestamps() {
        const now = new Date();
        const formatted = now.toISOString().slice(0, 19).replace('T', ' ');
        tokenTime.textContent = formatted;
        sessionTime.textContent = formatted;
    }

    function showSecurityAlert() {
        victimStatus.className = 'alert alert-danger';
        victimStatus.innerHTML = `<strong>Alert:</strong> ${victimName}, your account was accessed from another browser. Use the immediate logout option if this was not you.`;
        alertToast.show();
    }

    function simulateUnauthorizedAccess(type) {
        updateTimestamps();
        const resultArea = type === 'token' ? tokenResult : sessionResult;
        const stolenData = {
            email: victimEmail,
            name: victimName,
            account_id: 'user-101',
            last_active: new Date().toISOString().slice(0, 19).replace('T', ' '),
            auth_method: type === 'token' ? 'Bearer token' : 'Session cookie',
        };

        const message = type === 'token'
            ? `Browser B now has ${victimName}'s stolen bearer token and victim account metadata.`
            : `Browser B now has ${victimName}'s stolen session cookie and victim account metadata.`;

        resultArea.className = 'alert alert-danger small';
        resultArea.innerHTML = `<strong>${message}</strong>\nVictim data exposed to attacker browser B:\n${JSON.stringify(stolenData, null, 2)}`;
        showSecurityAlert();
    }

    document.getElementById('token-unauthorized-btn').addEventListener('click', () => {
        simulateUnauthorizedAccess('token');
    });

    document.getElementById('session-unauthorized-btn').addEventListener('click', () => {
        simulateUnauthorizedAccess('session');
    });

    document.getElementById('logout-all-btn').addEventListener('click', () => {
        alertToast.hide();
        victimStatus.className = 'alert alert-warning';
        victimStatus.innerHTML = `<strong>Action taken:</strong> ${victimName} has requested logout from all devices. This is a simulated security response.`;
    });
</script>
@endsection
