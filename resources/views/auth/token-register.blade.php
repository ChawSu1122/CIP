@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white">Token-based Registration</div>
                <div class="card-body">
                    <p>Register with <strong>token-based authentication</strong>. After registration, the server stores your user record and a small API token — we measure how many KB that uses.</p>
                    <p class="small text-muted">Example: register as <strong>Hla Hla</strong> to test token storage.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('token.register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name', 'Hla Hla') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="hlahla@example.com">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control" name="password" value="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" value="password" required>
                        </div>

                        <button type="submit" class="btn btn-success">Register with Token</button>
                        <a href="{{ route('session.register') }}" class="btn btn-outline-primary">Session Register instead</a>
                    </form>

                    <hr>
                    <h6>What gets stored?</h6>
                    <ul class="small mb-0">
                        <li><code>users</code> table — name, email, hashed password, API token</li>
                        <li>No <code>sessions</code> row — token is stateless</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
