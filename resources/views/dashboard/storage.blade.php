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


