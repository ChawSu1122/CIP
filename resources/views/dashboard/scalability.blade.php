@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Scalability Dashboard</span>
                    <a href="{{ route('comparison.dashboard') }}" class="btn btn-sm btn-outline-primary">Full Comparison</a>
                </div>
                <div class="card-body">
                    <p>Scalability measures how well each authentication method handles load: response time, memory usage, and database queries recorded on every login.</p>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header">Session-based</div>
                                <div class="card-body small">
                                    <p>Creates server-side session records. Each login writes to the sessions table and adds server memory load.</p>
                                    <p><strong>Disadvantage:</strong> scaling across multiple servers requires shared session storage.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header">Token-based</div>
                                <div class="card-body small">
                                    <p>Returns a bearer token. The client sends it on each API request without maintaining server session state.</p>
                                    <p><strong>Advantage:</strong> requests can be distributed across servers more easily.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="scalability-verdict" class="alert alert-info"></div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <canvas id="scalability-bar" height="140"></canvas>
                        </div>
                        <div class="col-md-4 mb-3">
                            <canvas id="scalability-pie" height="140"></canvas>
                        </div>
                    </div>

                    <h6>Recorded measurements</h6>
                    <ul id="scalability-stats" class="small"></ul>

                    <div class="mt-3">
                        <a href="{{ route('session.login') }}" class="btn btn-primary btn-sm">Test Session Login</a>
                        <a href="{{ route('token.login') }}" class="btn btn-success btn-sm">Test Token Login</a>
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
        ThesisCharts.initScalabilityPage('{{ route('thesis.data') }}');
    });
</script>
@endsection




