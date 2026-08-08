@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Session-based Login Page</div>
                <div class="card-body">
                    <p>This page uses the web session login endpoint. A successful login records the result and keeps the user logged in for normal features like comments and posts.</p>

                    <form id="session-login-form" action="{{ route('session.login.submit') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" name="email" type="email" class="form-control" value="" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input id="password" name="password" type="password" class="form-control" value="" required>
                                <button type="button" id="session-password-toggle" class="btn btn-outline-secondary" aria-label="Toggle password visibility">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <button type="button" id="session-login-button" class="btn btn-primary">Login with Session</button>
                    </form>

                    <div id="session-alert" class="alert mt-3 d-none" role="alert" aria-live="polite"></div>
                    <pre id="session-result" class="bg-light p-3 rounded mt-3 d-none" style="min-height: 120px; white-space: pre-wrap;"></pre>

                    <!-- <div class="mt-4">
                        <h5>Session result</h5>
                        <pre id="session-result" class="bg-light p-3 rounded" style="min-height: 120px; white-space: pre-wrap;"></pre>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const sessionAlert = document.getElementById('session-alert');
    const csrfToken = '{{ csrf_token() }}';

    const sessionButton = document.getElementById('session-login-button');
    const sessionPasswordToggle = document.getElementById('session-password-toggle');
    const sessionPassword = document.getElementById('password');

    sessionPasswordToggle.addEventListener('click', () => {
        const type = sessionPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        sessionPassword.setAttribute('type', type);
        sessionPasswordToggle.textContent = type === 'password' ? '👁️' : '❌';
    });

    function showSessionAlert(message, type = 'danger') {
        sessionAlert.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        sessionAlert.innerHTML = message;
        sessionAlert.classList.remove('d-none');
    }

    sessionButton.addEventListener('click', async () => {
        sessionAlert.classList.add('d-none');

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('remember', '1');

        try {
            const response = await fetch('{{ route('session.login.submit') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();
            if (response.ok && data.success) {
                window.location.href = '{{ route('home') }}';
                return;
            }

            const errorMessage = data.message ||
                (data.errors ? Object.values(data.errors).flat().join(' ') : 'Incorrect email or password. Please try again.');
            showSessionAlert(errorMessage, 'danger');
        } catch (error) {
            showSessionAlert('An unexpected error occurred. Please try again.', 'danger');
        }
    });
</script>
@endsection
