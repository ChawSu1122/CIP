@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header">Token Usage Demo</div>
                <div class="card-body">
                    <p>This page demonstrates how to login with the API and use the returned bearer token for authenticated requests.</p>

                    <form id="token-login-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" class="form-control" value="alice@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" class="form-control" value="password" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Login and get token</button>
                            <button type="button" id="get-user" class="btn btn-secondary ms-2">Get /api/user</button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5>Token result</h5>
                        <pre id="token-result" class="bg-light p-3 rounded" style="min-height: 120px; white-space: pre-wrap;"></pre>
                    </div>

                    <div class="mt-4">
                        <h5>How to use</h5>
                        <p>Send the bearer token in the <code>Authorization</code> header for protected API requests:</p>
                        <pre><code>Authorization: Bearer &lt;access_token&gt;</code></pre>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">API examples</div>
                <div class="card-body">
                    <p><strong>Login:</strong></p>
                    <pre><code>POST /api/login
Content-Type: application/json

{
  "email": "alice@example.com",
  "password": "password"
}
</code></pre>

                    <p><strong>Protected call:</strong></p>
                    <pre><code>GET /api/user
Authorization: Bearer &lt;access_token&gt;</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const loginForm = document.getElementById('token-login-form');
    const tokenResult = document.getElementById('token-result');
    const getUserButton = document.getElementById('get-user');
    let token = '';

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        tokenResult.textContent = 'Loading...';

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            });

            const data = await response.json();

            if (!response.ok) {
                tokenResult.textContent = JSON.stringify(data, null, 2);
                return;
            }

            token = data.access_token;
            tokenResult.textContent = JSON.stringify(data, null, 2);
        } catch (error) {
            tokenResult.textContent = error.message;
        }
    });

    getUserButton.addEventListener('click', async () => {
        if (!token) {
            tokenResult.textContent = 'Please login first to receive a token.';
            return;
        }

        tokenResult.textContent = 'Loading ...';

        try {
            const response = await fetch('/api/user', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });
            const data = await response.json();
            tokenResult.textContent = JSON.stringify(data, null, 2);
        } catch (error) {
            tokenResult.textContent = error.message;
        }
    });
</script>
