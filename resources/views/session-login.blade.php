@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Session-based Login Page</div>
                <div class="card-body">
                    <p>This page uses Laravel session authentication. Each successful login records scalability, storage, and security measurements to the database for thesis comparison.</p>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form id="session-login-form" method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email', 'alice@example.com') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control" name="password" value="password" required>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Login with Session</button>
                        <a href="{{ route('comparison.dashboard') }}" class="btn btn-outline-secondary">View Comparison</a>
                    </form>

                    <div class="mt-4">
                        <h5>What is measured on login?</h5>
                        <ul class="small mb-0">
                            <li><strong>Scalability:</strong> response time, memory usage, database queries</li>
                            <li><strong>Storage:</strong> session payload size written to the sessions table</li>
                            <li><strong>Security:</strong> login success/failure and CSRF-protected form submission</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
