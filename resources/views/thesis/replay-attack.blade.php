@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card mb-4">
                <div class="card-header">Replay Attack Demonstration</div>
                <div class="card-body">
                    <p>A <strong>replay attack</strong> happens when an attacker captures a valid credential (session cookie or bearer token) and reuses it later to impersonate the user — without knowing the password.</p>
                    <p class="mb-0">This demo shows how both authentication methods can be vulnerable, and how mitigations like token revocation and session expiry reduce the risk.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card border-success h-100">
                        <div class="card-header bg-success text-white">Token Replay Attack</div>
                        <div class="card-body">
                            <ol class="small">
                                <li>User logs in via <a href="{{ route('token.login') }}">Token Login</a> and receives a bearer token.</li>
                                <li>Attacker captures the token (network sniffing, XSS, leaked logs).</li>
                                <li>Attacker replays the token in an API request — no password needed.</li>
                            </ol>

                            <div class="mb-3">
                                <label class="form-label">Step 1 — Get a token (victim login)</label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="email" id="token-email" class="form-control" value="alice@example.com">
                                    <input type="password" id="token-password" class="form-control" value="password">
                                    <button id="token-login-btn" class="btn btn-success">Login</button>
                                </div>
                                <textarea id="captured-token" class="form-control form-control-sm" rows="2" placeholder="Captured bearer token appears here..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Step 2 — Attacker replays captured token</label>
                                <button id="replay-token-btn" class="btn btn-danger btn-sm">Replay Token Attack</button>
                                <button id="revoke-token-btn" class="btn btn-outline-secondary btn-sm">Revoke Token (mitigation)</button>
                            </div>

                            <pre id="token-replay-result" class="bg-light p-2 rounded small" style="min-height:100px"></pre>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white">Session Replay Attack</div>
                        <div class="card-body">
                            <ol class="small">
                                <li>User logs in via <a href="{{ route('session.login') }}">Session Login</a> — browser stores a session cookie.</li>
                                <li>Attacker steals the <code>laravel_session</code> cookie (XSS, MITM, shared device).</li>
                                <li>Attacker replays the session ID — server accepts it as a valid login.</li>
                            </ol>

                            <div class="mb-3">
                                <label class="form-label">Step 1 — Get session ID (victim must be logged in)</label>
                                <button id="get-session-btn" class="btn btn-primary btn-sm">Show My Session ID</button>
                                <p class="small text-muted mt-1">Log in first at <a href="{{ route('session.login') }}">Session Login</a>, then click above.</p>
                                <textarea id="captured-session" class="form-control form-control-sm mt-2" rows="2" placeholder="Captured session ID appears here..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Step 2 — Attacker replays captured session ID</label>
                                <button id="replay-session-btn" class="btn btn-danger btn-sm">Replay Session Attack</button>
                            </div>

                            <pre id="session-replay-result" class="bg-light p-2 rounded small" style="min-height:100px"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Thesis Analysis — Which is more vulnerable?</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Aspect</th>
                                    <th>Session-based</th>
                                    <th>Token-based</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Replay target</td>
                                    <td>Session cookie (<code>laravel_session</code>)</td>
                                    <td>Bearer token in Authorization header</td>
                                </tr>
                                <tr>
                                    <td>Auto-sent by browser?</td>
                                    <td>Yes — increases CSRF + hijack risk</td>
                                    <td>No — must be stored manually by client</td>
                                </tr>
                                <tr>
                                    <td>Replay works until</td>
                                    <td>Session expires or user logs out</td>
                                    <td>Token is revoked or expires</td>
                                </tr>
                                <tr>
                                    <td>Mitigation shown in demo</td>
                                    <td>Session expiry (configurable lifetime)</td>
                                    <td>Token revocation via logout API</td>
                                </tr>
                                <tr>
                                    <td>Thesis conclusion</td>
                                    <td colspan="2">Both are vulnerable to replay if credentials are stolen. Session auth adds CSRF protection; token auth requires explicit expiry and secure client storage.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    async function postJson(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        });
        return { ok: response.ok, data: await response.json() };
    }

    document.getElementById('token-login-btn').addEventListener('click', async () => {
        const result = document.getElementById('token-replay-result');
        result.textContent = 'Logging in...';
        const { ok, data } = await postJson('/api/login', {
            email: document.getElementById('token-email').value,
            password: document.getElementById('token-password').value,
        });
        if (ok) {
            document.getElementById('captured-token').value = data.access_token;
            result.textContent = JSON.stringify({ step: 'Victim logged in', user: data.user }, null, 2);
        } else {
            result.textContent = JSON.stringify(data, null, 2);
        }
    });

    document.getElementById('replay-token-btn').addEventListener('click', async () => {
        const token = document.getElementById('captured-token').value.trim();
        const result = document.getElementById('token-replay-result');
        if (!token) { result.textContent = 'No token to replay. Login first.'; return; }
        const { ok, data } = await postJson('{{ route('thesis.replay.token') }}', { token });
        result.textContent = JSON.stringify(data, null, 2);
        result.className = 'p-2 rounded small ' + (ok ? 'bg-danger-subtle' : 'bg-success-subtle');
    });

    document.getElementById('revoke-token-btn').addEventListener('click', async () => {
        const token = document.getElementById('captured-token').value.trim();
        const result = document.getElementById('token-replay-result');
        if (!token) { result.textContent = 'No token to revoke.'; return; }
        const response = await fetch('/api/logout', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        });
        const data = await response.json();
        result.textContent = JSON.stringify({ step: 'Token revoked (mitigation)', ...data }, null, 2);
        result.className = 'p-2 rounded small bg-info-subtle';
    });

    document.getElementById('get-session-btn').addEventListener('click', async () => {
        const result = document.getElementById('session-replay-result');
        result.textContent = 'Fetching session info...';
        const response = await fetch('{{ route('thesis.replay.session-info') }}', {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        if (response.ok) {
            document.getElementById('captured-session').value = data.session_id;
        }
        result.textContent = JSON.stringify(data, null, 2);
    });

    document.getElementById('replay-session-btn').addEventListener('click', async () => {
        const sessionId = document.getElementById('captured-session').value.trim();
        const result = document.getElementById('session-replay-result');
        if (!sessionId) { result.textContent = 'No session ID to replay. Get session ID first.'; return; }
        const { ok, data } = await postJson('{{ route('thesis.replay.session') }}', { session_id: sessionId });
        result.textContent = JSON.stringify(data, null, 2);
        result.className = 'p-2 rounded small ' + (ok ? 'bg-danger-subtle' : 'bg-success-subtle');
    });
</script>
@endsection
