@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">API Documentation</div>
                <div class="card-body">
                    <p>This application supports both <strong>session-based</strong> authentication for the web interface and <strong>token-based</strong> authentication for API access.</p>
                    <p>Use the <a href="{{ route('session.login') }}">Session Login</a> page to login with cookies, and the <a href="{{ route('token.login') }}">Token Login</a> page to login via API token.</p>
                    <p>For full analysis, visit the <a href="{{ route('comparison.dashboard') }}">Comparison</a> page and the individual dashboard pages for scalability, storage, and security.</p>

                    <h4>1. Session-based Authentication</h4>
                    <p>Use the standard web login page at <code>/login</code>. When authenticated, Laravel stores a session on the server and the browser receives a cookie.</p>

                    <h4>2. Token-based Authentication</h4>
                    <p>Use the API login endpoint to receive a bearer token.</p>
                    <pre><code>POST /api/login
Content-Type: application/json

{
  "email": "alice@gmail.com",
  "password": "password"
}
</code></pre>

                    <p>Successful response:</p>
                    <pre><code>{
  "access_token": "...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Alice",
    "email": "alice@gmail.com"
  }
}
</code></pre>

                    <h4>3. Using the Bearer Token</h4>
                    <p>Include the token in the <code>Authorization</code> header for protected API requests:</p>
                    <pre><code>Authorization: Bearer your-token-here</code></pre>

                    <h4>4. Example API Endpoints</h4>
                    <ul>
                        <li><code>GET /api/posts</code> - List blog posts</li>
                        <li><code>GET /api/posts/{id}</code> - Get a single post with comments</li>
                        <li><code>POST /api/posts</code> - Create a new post</li>
                        <li><code>POST /api/posts/{id}/comments</code> - Add a comment</li>
                        <li><code>GET /api/categories</code> - List categories</li>
                    </ul>

                    <h4>5. Practical Comparison</h4>
                    <p><strong>Scalability:</strong> token-based auth is stateless and easier to scale across servers. Session auth keeps state on the server.</p>
                    <p><strong>Storage:</strong> session auth stores user session data on the server (database, Redis, or files). Token auth stores authentication state in the token itself.</p>
                    <p><strong>Security:</strong> session auth relies on secure cookies and CSRF protections. Token auth relies on bearer token secrecy and can be used by mobile or third-party clients.</p>

                    <h4>6. Testing the API</h4>
                    <p>After login, use the returned bearer token for subsequent requests. The same community features are available via API: posts, comments, categories.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
