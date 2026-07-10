@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Thesis Experiment Results</span>
                    <a href="{{ route('comparison.dashboard') }}" class="btn btn-sm btn-outline-primary">Comparison Dashboard</a>
                </div>
                <div class="card-body">
                    <p>Detailed performance metrics collected from session and token authentication requests.</p>
                    <div class="mb-5">
                        <h5>1. Login Response Time</h5>
                        <canvas id="login-chart" style="max-width:800px" height="120"></canvas>
                    </div>
                    <div class="mb-5">
                        <h5>2. Action Response Time</h5>
                        <canvas id="action-chart" style="max-width:800px" height="120"></canvas>
                    </div>
                    <div class="mb-5">
                        <h5>3. Memory Usage</h5>
                        <canvas id="memory-chart" style="max-width:800px" height="120"></canvas>
                    </div>
                    <div class="mb-5">
                        <h5>4. Query Count</h5>
                        <canvas id="query-chart" style="max-width:800px" height="120"></canvas>
                    </div>
                    <div class="mb-5">
                        <h5>5. Storage per Login (bytes)</h5>
                        <canvas id="storage-chart" style="max-width:800px" height="120"></canvas>
                    </div>
                    <div class="alert alert-secondary">
                        Perform logins on <a href="{{ route('session.login') }}">Session Login</a> and <a href="{{ route('token.login') }}">Token Login</a>, then use forum actions to populate action metrics.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.thesis-charts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const response = await fetch('{{ route('thesis.data') }}');
        const data = await response.json();

        const loginRows = (data.loginMetrics || []).filter(r => r.runs > 0);
        const actionRows = (data.actionMetrics || []).filter(r => r.runs > 0);

        const loginLabels = loginRows.map(r => r.auth_type === 'session' ? 'Session Login' : 'Token Login');
        const actionLabels = [...new Set(actionRows.map(r => r.action.replace(/_/g, ' ')))];

        function sessionValue(rows, field, action) {
            const row = rows.find(r => r.auth_type === 'session' && (!action || r.action === action));
            return row ? row[field] : 0;
        }
        function tokenValue(rows, field, action) {
            const row = rows.find(r => r.auth_type === 'token' && (!action || r.action === action));
            return row ? row[field] : 0;
        }

        const charts = [
            ['login-chart', loginLabels, [
                { label: 'Duration (ms)', data: loginRows.map(r => r.avg_duration), backgroundColor: loginRows.map(r => r.auth_type === 'session' ? '#0d6efd' : '#20c997') },
            ]],
            ['action-chart', actionLabels, [
                { label: 'Session', data: actionLabels.map(a => sessionValue(actionRows, 'avg_duration', a.replace(/ /g, '_'))), backgroundColor: '#0d6efd' },
                { label: 'Token', data: actionLabels.map(a => tokenValue(actionRows, 'avg_duration', a.replace(/ /g, '_'))), backgroundColor: '#20c997' },
            ]],
            ['memory-chart', loginLabels, [
                { label: 'Memory (bytes)', data: loginRows.map(r => r.avg_memory), backgroundColor: loginRows.map(r => r.auth_type === 'session' ? '#0d6efd' : '#20c997') },
            ]],
            ['query-chart', loginLabels, [
                { label: 'Queries', data: loginRows.map(r => r.avg_queries), backgroundColor: loginRows.map(r => r.auth_type === 'session' ? '#0d6efd' : '#20c997') },
            ]],
            ['storage-chart', loginLabels, [
                { label: 'Storage (bytes)', data: loginRows.map(r => r.avg_storage_bytes), backgroundColor: loginRows.map(r => r.auth_type === 'session' ? '#0d6efd' : '#20c997') },
            ]],
        ];

        charts.forEach(([id, labels, datasets]) => {
            const el = document.getElementById(id);
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: { labels, datasets },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } },
            });
        });
    });
</script>
@endsection
