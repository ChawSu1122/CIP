<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Analysis Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            /* background: linear-gradient(135deg, #0f172a 0%, #111827 100%); */
            background: white
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .analysis-shell {
            width: min(92vw, 540px);
        }

        .analysis-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.35);
        }

        .analysis-card .card-header {
            background: #0f172a;
            color: #fff;
            border-bottom: 0;
            padding: 1.25rem 1.5rem;
        }

        .analysis-card .card-body {
            padding: 2rem 1.5rem 1.5rem;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="analysis-shell">
        <div class="card analysis-card">
            <div class="card-header">
                <h1 class="h4 mb-0">Analysis Login</h1>
            </div>
            <div class="card-body">
                <p class="mb-4 text-secondary">This login is reserved for analysis users who manage the Security Testing Dashboard.</p>

                <form id="analysis-login-form" action="{{ route('analysis.login.submit') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="analysis-email" class="form-label">Email Address</label>
                        <input id="analysis-email" name="email" type="email" class="form-control" required autocomplete="email" autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="analysis-password" class="form-label">Password</label>
                        <div style="position: relative;">
                            <input id="analysis-password" name="password" type="password" class="form-control" required autocomplete="current-password" style="padding-right: 40px;">
                            <button type="button" id="analysis-password-toggle" class="btn btn-link" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; cursor: pointer; color: #666;">
                                <svg id="analysis-eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="analysis-remember">
                        <label class="form-check-label" for="analysis-remember">Remember me</label>
                    </div>

                    <button type="submit" id="analysis-login-button" class="btn btn-dark w-100">Login as Analysis</button>
                </form>

                <div id="analysis-alert" class="alert mt-3 d-none" role="alert" aria-live="polite"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordField = document.getElementById('analysis-password');
            const toggleButton = document.getElementById('analysis-password-toggle');
            const eyeIcon = document.getElementById('analysis-eye-icon');
            const form = document.getElementById('analysis-login-form');
            const alertBox = document.getElementById('analysis-alert');
            const csrfToken = '{{ csrf_token() }}';

            if (toggleButton && passwordField && eyeIcon) {
                toggleButton.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                    } else {
                        passwordField.type = 'password';
                        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                    }
                });
            }

            function showAlert(message, type = 'danger') {
                alertBox.className = `alert alert-${type} mt-3`;
                alertBox.textContent = message;
                alertBox.classList.remove('d-none');
            }

            if (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    alertBox.classList.add('d-none');

                    const formData = new FormData(form);
                    formData.append('_token', csrfToken);

                    try {
                        const response = await fetch('{{ route('analysis.login.submit') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.href = data.redirect || '{{ route('dashboard') }}';
                            return;
                        }

                        const message = data.message || 'Incorrect email or password. Please try again.';
                        showAlert(message, 'danger');
                    } catch (error) {
                        showAlert('An unexpected error occurred. Please try again.', 'danger');
                    }
                });
            }
        });
    </script>
</body>
</html>
