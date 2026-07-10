<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Token Login</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        .form { max-width: 400px; margin: 0 auto; }
        label { display:block; margin-top: 1rem; }
        input { width:100%; padding:8px; margin-top:4px }
        button { margin-top: 1rem; padding:10px 16px }
        .error { color: red; margin-top: 1rem }
    </style>
</head>
<body>
    <div class="form">
        <h2>Token Login</h2>
        <form id="token-login-form">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required />

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required />

            <button type="submit">Login</button>
            <div id="error" class="error" role="alert" aria-live="polite"></div>
        </form>
    </div>

    <script>
    document.getElementById('token-login-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errEl = document.getElementById('error');
        errEl.textContent = '';

        try {
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password }),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                errEl.textContent = data.message || 'Login failed';
                return;
            }

            const data = await res.json();
            if (data.token) {
                localStorage.setItem('api_token', data.token);
                window.location.href = '/home';
            } else {
                errEl.textContent = 'No token returned';
            }
        } catch (err) {
            errEl.textContent = 'Network error';
        }
    });
    </script>
</body>
</html>
