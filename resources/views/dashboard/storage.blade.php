@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Storage Dashboard</span>
                    <a href="{{ route('comparison.dashboard') }}" class="btn btn-sm btn-outline-primary">Full Comparison</a>
                </div>
                <div class="card-body">
                    <p>Storage compares how many bytes each method keeps on the server after login — session payload size vs API token stored in the database.</p>

                    @if (!empty($registrationResult))
                        <div class="alert alert-success">
                            <strong>Latest registration:</strong> {{ $registrationResult['name'] }} —
                            <small class="text-muted">method: {{ $registrationResult['auth_method'] }}</small>
                            <div class="mt-2">
                                <span class="me-2">Total: <strong>{{ $registrationResult['total_kb'] }} KB</strong></span>
                                <span class="me-2">User row: <strong>{{ $registrationResult['user_row_kb'] }} KB</strong></span>
                                <span class="me-2">Session row: <strong>{{ $registrationResult['session_row_kb'] }} KB</strong></span>
                                <span class="me-2">Token: <strong>{{ $registrationResult['token_kb'] }} KB</strong></span>
                            </div>
                        </div>
                    @endif

                    @if (!empty($registrationComparison))
                        <div class="card mb-3">
                            <div class="card-header">Registration comparison</div>
                            <div class="card-body small">
                                <p><strong>Winner:</strong> {{ ucfirst($registrationComparison['winner']) }} — {{ $registrationComparison['verdict'] }}</p>
                                <p><strong>Percent difference:</strong> {{ $registrationComparison['percent_difference'] }}%</p>
                                <h6>Recent registrations</h6>
                                <ul class="small">
                                    @foreach($registrationComparison['recent'] as $r)
                                        <li>{{ $r['created_at'] }} — {{ $r['user_name'] }} ({{ $r['auth_method'] }}): {{ $r['total_kb'] }} KB — user: {{ $r['user_row_kb'] }} KB, session: {{ $r['session_row_kb'] }} KB, token: {{ $r['token_kb'] }} KB</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (!empty($registrationResult))
                        <div class="mb-3">
                            <div class="card border-info">
                                <div class="card-header">Latest Registration Result</div>
                                <div class="card-body small">
                                    <p><strong>Name:</strong> {{ $registrationResult['name'] }}</p>
                                    <p><strong>Auth method:</strong> {{ ucfirst($registrationResult['auth_method']) }}</p>
                                    <p><strong>Total:</strong> {{ $registrationResult['total_kb'] }} KB</p>
                                    <p class="mb-0"><strong>Breakdown:</strong>
                                        User: {{ $registrationResult['user_row_kb'] }} KB ·
                                        Session: {{ $registrationResult['session_row_kb'] }} KB ·
                                        Token: {{ $registrationResult['token_kb'] }} KB
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($registrationComparison))
                        <div class="mb-3">
                            <div class="card border-secondary">
                                <div class="card-header">Registration Comparison (aggregated)</div>
                                <div class="card-body small">
                                    <p><strong>Winner:</strong> {{ ucfirst($registrationComparison['winner']) }} — {{ $registrationComparison['verdict'] }}</p>
                                    <p><strong>Percent difference:</strong> {{ $registrationComparison['percent_difference'] }}%</p>
                                    <p class="small text-muted">Methodology: {{ $registrationComparison['methodology'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header">Session-based</div>
                                <div class="card-body small">
                                    <p>Full session data is stored in the <code>sessions</code> table (serialized payload).</p>
                                    <p><strong>Disadvantage:</strong> storage grows with every active logged-in user.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header">Token-based</div>
                                <div class="card-body small">
                                    <p>Only an 80-character token string is stored on the user record.</p>
                                    <p><strong>Advantage:</strong> typically much smaller per-user server footprint.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="storage-verdict" class="alert alert-info"></div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <canvas id="storage-bar" height="140"></canvas>
                        </div>
                        <div class="col-md-4 mb-3">
                            <canvas id="storage-pie" height="140"></canvas>
                        </div>
                    </div>

                    <h6>Recorded measurements</h6>
                    <ul id="storage-stats" class="small"></ul>

                    <div class="mt-3">
                        <a href="{{ route('session.login') }}" class="btn btn-primary btn-sm">Test Session Login</a>
                        <a href="{{ route('token.login') }}" class="btn btn-success btn-sm">Test Token Login</a>
                        <button id="refresh-metrics" class="btn btn-outline-secondary btn-sm">Refresh</button>
                    </div>

                    @if (!empty($registrationComparison))
                        <div class="card mt-4">
                            <div class="card-header">All registration records</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th>Method</th>
                                                <th>Total KB</th>
                                                <th>User KB</th>
                                                <th>Session KB</th>
                                                <th>Token KB</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registrationComparison['recent'] as $record)
                                                <tr>
                                                    <td>{{ $record['created_at'] }}</td>
                                                    <td>{{ $record['user_name'] }}</td>
                                                    <td>{{ ucfirst($record['auth_method']) }}</td>
                                                    <td>{{ $record['total_kb'] }}</td>
                                                    <td>{{ $record['user_row_kb'] }}</td>
                                                    <td>{{ $record['session_row_kb'] }}</td>
                                                    <td>{{ $record['token_kb'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Session summary</div>
                                    <div class="card-body small">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr><th>Total registrations</th><td>{{ $registrationComparison['session']['register_count'] }}</td></tr>
                                            <tr><th>Average total KB</th><td>{{ $registrationComparison['session']['avg_kb'] }}</td></tr>
                                            <tr><th>Total KB</th><td>{{ $registrationComparison['session']['total_kb'] }}</td></tr>
                                            <tr><th>Min KB</th><td>{{ $registrationComparison['session']['min_kb'] }}</td></tr>
                                            <tr><th>Max KB</th><td>{{ $registrationComparison['session']['max_kb'] }}</td></tr>
                                            <tr><th>Avg user KB</th><td>{{ $registrationComparison['session']['avg_user_row_kb'] }}</td></tr>
                                            <tr><th>Avg session KB</th><td>{{ $registrationComparison['session']['avg_session_row_kb'] }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Token summary</div>
                                    <div class="card-body small">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr><th>Total registrations</th><td>{{ $registrationComparison['token']['register_count'] }}</td></tr>
                                            <tr><th>Average total KB</th><td>{{ $registrationComparison['token']['avg_kb'] }}</td></tr>
                                            <tr><th>Total KB</th><td>{{ $registrationComparison['token']['total_kb'] }}</td></tr>
                                            <tr><th>Min KB</th><td>{{ $registrationComparison['token']['min_kb'] }}</td></tr>
                                            <tr><th>Max KB</th><td>{{ $registrationComparison['token']['max_kb'] }}</td></tr>
                                            <tr><th>Avg user KB</th><td>{{ $registrationComparison['token']['avg_user_row_kb'] }}</td></tr>
                                            <tr><th>Avg token KB</th><td>{{ $registrationComparison['token']['avg_token_kb'] }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.thesis-charts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        ThesisCharts.initStoragePage('{{ route('thesis.data') }}');
    });
</script>
@endsection




