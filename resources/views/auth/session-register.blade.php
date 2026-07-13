@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">Session-based Registration</div>
                <div class="card-body">
                    <p>Register with <strong>session-based authentication</strong>. After registration, the server stores your user record <em>and</em> a session payload — we measure how many KB that uses.</p>
                    <p class="small text-muted">Example: register as <strong>Mg Mg</strong> to test session storage.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('session.register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name', 'Mg Mg') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="mgmg@example.com">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control" name="password" value="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" value="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Register with Session</button>
                        <a href="{{ route('token.register') }}" class="btn btn-outline-success">Token Register instead</a>
                    </form>

                    <hr>
                    <h6>What gets stored?</h6>
                    <ul class="small mb-0">
                        <li><code>users</code> table — name, email, hashed password</li>
                        <li><code>sessions</code> table — session payload (login state)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
