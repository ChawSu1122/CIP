@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Session-based Login Page</div>
                <div class="card-body">
                    <p>This page uses the web session login endpoint. A successful login records the result and keeps the user logged in for normal features like comments and posts.</p>

                    <form id="session-login-form">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" name="email" type="email" class="form-control" value="alice@gmail.com" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" class="form-control" value="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Login with Session</button>
                    </form>

                    <div id="session-alert" class="alert mt-3 d-none"></div>

                    <div class="mt-4">
                        <h5>Session result</h5>
                        <pre id="session-result" class="bg-light p-3 rounded" style="min-height: 120px; white-space: pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const loginForm = document.getElementById('session-login-form');
    const sessionResult = document.getElementById('session-result');
    const sessionAlert = document.getElementById('session-alert');
    const csrfToken = '{{ csrf_token() }}';

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        sessionResult.textContent = 'Loading...';
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
            sessionResult.textContent = JSON.stringify(data, null, 2);

            if (response.ok && data.success) {
                sessionAlert.className = 'alert alert-success mt-3';
                sessionAlert.textContent = 'Login successful — web session established and you can now use comments/posts.';
                sessionAlert.classList.remove('d-none');
            } else {
                sessionAlert.className = 'alert alert-danger mt-3';
                sessionAlert.textContent = data.message || 'Login failed.';
                sessionAlert.classList.remove('d-none');
            }
        } catch (error) {
            sessionResult.textContent = error.message;
        }
    });
</script>
@endsection
