@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-2">Session vs Token Authentication Comparison</h1>
                    <p class="text-muted mb-3">Community Interaction Platform — thesis experiment measuring scalability, storage, and security when users log in via session-based or token-based authentication.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('session.login') }}" class="btn btn-primary btn-sm">Session Login</a>
                        <a href="{{ route('token.login') }}" class="btn btn-success btn-sm">Token Login</a>
                        <a href="{{ route('dashboard.scalability') }}" class="btn btn-outline-secondary btn-sm">Scalability</a>
                        <a href="{{ route('dashboard.storage') }}" class="btn btn-outline-secondary btn-sm">Storage</a>
                        <a href="{{ route('dashboard.security') }}" class="btn btn-outline-secondary btn-sm">Security</a>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Advantages &amp; Disadvantages</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Aspect</th>
                                    <th>Session-based</th>
                                    <th>Token-based</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Advantages</strong></td>
                                    <td>HttpOnly cookies, built-in CSRF protection, easy server-side logout, familiar for browser apps</td>
                                    <td>Stateless API requests, easier horizontal scaling, works well for mobile/SPA clients, no cookie dependency</td>
                                </tr>
                                <tr>
                                    <td><strong>Disadvantages</strong></td>
                                    <td>Server-side session storage grows with users, harder to scale across servers, CSRF setup required</td>
                                    <td>Token leakage risk, client must store token safely, expiration/revocation must be designed explicitly</td>
                                </tr>
                                <tr>
                                    <td><strong>Best for</strong></td>
                                    <td>Traditional web applications with server-rendered pages</td>
                                    <td>REST APIs, mobile apps, single-page applications</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="metrics-empty" class="alert alert-warning d-none">
                No measurements yet. Log in using both <a href="{{ route('session.login') }}">Session Login</a> and <a href="{{ route('token.login') }}">Token Login</a> to collect comparison data.
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white">Scalability</div>
                        <div class="card-body">
                            <p class="small text-muted">Response time, memory usage, and database queries per login.</p>
                            <canvas id="scalability-score-chart" height="180"></canvas>
                            <canvas id="scalability-duration-chart" class="mt-3" height="160"></canvas>
                            <div id="scalability-verdict" class="alert alert-info mt-3 mb-0 small"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white">Storage</div>
                        <div class="card-body">
                            <p class="small text-muted">Server bytes used per login (session payload vs API token).</p>
                            <canvas id="storage-bytes-chart" height="180"></canvas>
                            <canvas id="storage-total-chart" class="mt-3" height="160"></canvas>
                            <div id="storage-verdict" class="alert alert-info mt-3 mb-0 small"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-warning">
                        <div class="card-header bg-warning">Security</div>
                        <div class="card-body">
                            <p class="small text-muted">Success rate, failed attempts, and built-in protection features.</p>
                            <canvas id="security-score-chart" height="180"></canvas>
                            <canvas id="security-success-chart" class="mt-3" height="160"></canvas>
                            <div id="security-verdict" class="alert alert-info mt-3 mb-0 small"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Overall Comparison Scores</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="overall-bar-chart" height="120"></canvas>
                        </div>
                        <div class="col-md-4">
                            <canvas id="overall-pie-chart" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Live Measurement Summary</span>
                    <button id="refresh-metrics" class="btn btn-sm btn-outline-primary">Refresh</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0" id="metrics-table">
                            <thead>
                                <tr>
                                    <th>Criterion</th>
                                    <th>Session</th>
                                    <th>Token</th>
                                    <th>Better</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.thesis-charts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        ThesisCharts.initComparisonPage('{{ route('thesis.data') }}');
    });
</script>
@endsection
