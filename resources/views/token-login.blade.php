@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Token-based Login Page</div>
                <div class="card-body">
                    <p>This page uses the API login endpoint. Each successful login records scalability, storage, and security measurements to the database for thesis comparison.</p>

                    <form id="api-login-form">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" name="email" type="email" class="form-control" value="" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input id="password" name="password" type="password" class="form-control" value="" required>
                                <button type="button" id="token-password-toggle" class="btn btn-outline-secondary" aria-label="Toggle password visibility">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <button type="button" id="api-login-button" class="btn btn-success">Login with token</button>
                        <!-- <a href="{{ route('comparison.dashboard') }}" class="btn btn-outline-secondary">View Comparison</a> -->
                    </form>

                    <div id="login-alert" class="alert mt-3 d-none" role="alert" aria-live="polite"></div>
                    <pre id="token-result" class="bg-light p-3 rounded mt-3 d-none" style="min-height: 120px; white-space: pre-wrap;"></pre>

                    <!-- <div class="mt-4">
                        <h5>Token result</h5>
                        <pre id="token-result" class="bg-light p-3 rounded" style="min-height: 120px; white-space: pre-wrap;"></pre>
                    </div> -->

                    <!-- <div class="mt-4">
                        <h5>What is measured on login?</h5>
                        <ul class="small mb-0">
                            <li><strong>Scalability:</strong> response time, memory usage, database queries</li>
                            <li><strong>Storage:</strong> API token bytes stored on the user record</li>
                            <li><strong>Security:</strong> login success/failure and bearer token exposure characteristics</li>
                        </ul>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const loginAlert = document.getElementById('login-alert');
    const csrfToken = '{{ csrf_token() }}';

    const apiLoginButton = document.getElementById('api-login-button');
    const tokenPasswordToggle = document.getElementById('token-password-toggle');
    const tokenPassword = document.getElementById('password');

    function showTokenAlert(message, type = 'danger') {
        loginAlert.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        loginAlert.textContent = message;
        loginAlert.classList.remove('d-none');
    }

    tokenPasswordToggle.addEventListener('click', () => {
        const type = tokenPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        tokenPassword.setAttribute('type', type);
        tokenPasswordToggle.textContent = type === 'password' ? '👁️' : '❌';
    });

    apiLoginButton.addEventListener('click', async () => {
        loginAlert.classList.add('d-none');

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    email,
                    password,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMessage = data.message ||
                    (data.errors ? Object.values(data.errors).flat().join(' ') : 'Incorrect email or password. Please try again.');
                showTokenAlert(errorMessage, 'danger');
                return;
            }

            await fetch('/token-login-state', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const sessionPayload = new URLSearchParams();
            sessionPayload.append('_token', csrfToken);
            sessionPayload.append('email', email);
            sessionPayload.append('password', password);
            sessionPayload.append('remember', '1');

            const sessionResponse = await fetch('/login', {
                method: 'POST',
                credentials: 'same-origin',
                redirect: 'manual',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: sessionPayload,
            });

            const sessionOk =
                sessionResponse.type === 'opaqueredirect' ||
                sessionResponse.status === 302 ||
                sessionResponse.status === 303 ||
                sessionResponse.ok;

            if (sessionOk) {
                window.location.href = '{{ route('home') }}';
                return;
            }

            loginAlert.className = 'alert alert-success mt-3';
            loginAlert.innerHTML = 'Login successful — token received and web session created. <a href="{{ route('comparison.dashboard') }}">View comparison charts</a>';
            loginAlert.classList.remove('d-none');
        } catch (error) {
            showTokenAlert('An unexpected error occurred. Please try again.', 'danger');
        }
    });
</script>
@endsection
