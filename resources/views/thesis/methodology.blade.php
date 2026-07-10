@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">Thesis Methodology</div>
                <div class="card-body">
                    <h5>System Design</h5>
                    <p>The community platform uses one shared backend for session-based and token-based authentication, so both methods protect the same features.</p>

                    <h5>Session-Based Authentication</h5>
                    <ul>
                        <li>User submits email and password.</li>
                        <li>Server verifies credentials.</li>
                        <li>Server creates a session and stores a session ID in a browser cookie.</li>
                        <li>Subsequent requests use the cookie to access protected pages.</li>
                    </ul>

                    <h5>Token-Based Authentication</h5>
                    <ul>
                        <li>User submits email and password.</li>
                        <li>Server verifies credentials.</li>
                        <li>Server generates a token and returns it to the client.</li>
                        <li>Client sends the token in the Authorization header on each request.</li>
                    </ul>

                    <h5>Evaluation Metrics</h5>
                    <ul>
                        <li>Login response time</li>
                        <li>Request processing time for common actions</li>
                        <li>Server memory usage</li>
                        <li>Database query count</li>
                    </ul>

                    <h5>Experiment Design</h5>
                    <p>The app records metrics for both login and action requests, then displays results on the experiment page.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
