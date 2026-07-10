@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Security Dashboard</span>
                    <a href="{{ route('comparison.dashboard') }}" class="btn btn-sm btn-outline-primary">Full Comparison</a>
                </div>
                <div class="card-body">
                    <p>Security compares login success rates, failed attempts, and built-in protection features for each authentication method.</p>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header">Session-based</div>
                                <div class="card-body small">
                                    <p>Uses HttpOnly cookies with CSRF protection on web forms.</p>
                                    <p><strong>Risk:</strong> session hijacking if cookies are stolen.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header">Token-based</div>
                                <div class="card-body small">
                                    <p>Uses Authorization bearer header — not sent automatically by browsers.</p>
                                    <p><strong>Risk:</strong> token leakage from client storage (localStorage, logs).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="security-verdict" class="alert alert-info"></div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <canvas id="security-bar" height="140"></canvas>
                        </div>
                        <div class="col-md-4 mb-3">
                            <canvas id="security-pie" height="140"></canvas>
                        </div>
                    </div>

                    <h6>Recorded measurements</h6>
                    <ul id="security-stats" class="small"></ul>

                    <div class="mt-3">
                        <a href="{{ route('session.login') }}" class="btn btn-primary btn-sm">Test Session Login</a>
                        <a href="{{ route('token.login') }}" class="btn btn-success btn-sm">Test Token Login</a>
                        <a href="{{ route('thesis.replay') }}" class="btn btn-danger btn-sm">Replay Attack Demo</a>
                        <button id="refresh-metrics" class="btn btn-outline-secondary btn-sm">Refresh</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.thesis-charts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        ThesisCharts.initSecurityPage('{{ route('thesis.data') }}');
    });
</script>
@endsection
