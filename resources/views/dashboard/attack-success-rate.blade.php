@extends('dashboard.layout')

@section('title', 'Attack Success Rate')

@section('content')
@php
    use App\Models\ExperimentMetric;

    $tokenPhish = ExperimentMetric::where('action', 'link_clicked')
        ->where('victim_authentication_type', 'token')
        ->latest('created_at')
        ->first();

    $sessionPhish = ExperimentMetric::where('action', 'link_clicked')
        ->where('victim_authentication_type', 'session')
        ->latest('created_at')
        ->first();

    $sessionAsrMetrics = ExperimentMetric::where('action', 'attack_success_rate_test')
        ->where('victim_authentication_type', 'session')
        ->get();
    $tokenAsrMetrics = ExperimentMetric::where('action', 'attack_success_rate_test')
        ->where('victim_authentication_type', 'token')
        ->get();
@endphp

<div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-3">
    <div>
        <h1 class="page-title">Attack Success Rate After Logout</h1>
    </div>
    <button id="reset-attack-boxes-btn" type="button" class="btn btn-outline-secondary btn-sm">Reset</button>
</div>

<div class="row row-cols-1 row-cols-lg-2 gx-4 gy-4 mt-3">
    <div class="col">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white border-0">
                <h2 class="h5 mb-1">Session Hijacking Attack</h2>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Browser A (victim)</span>
                    <span id="session-victim-status" class="fw-semibold">{{ $sessionPhish ? $sessionPhish->victim_name . ' is logged in and clicks the phishing link.' : '—' }}</span>
                </li>
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Captured session ID</span>
                    <span id="session-victim-session" class="fw-semibold text-break">{{ $sessionPhish?->victim_session_id ?? '—' }}</span>
                </li>
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Authentication Method</span>
                    {{-- <span class="fw-semibold">Session-Based</span> --}}
                    <span class="fw-semibold">@if($sessionPhish) Session-Based @else — @endif</span>
                </li>
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Time</span>
                    <span id="session-phish-time" class="fw-semibold">{{ $sessionPhish?->created_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
                </li>
                <li class="list-group-item py-3">
                    <textarea id="captured-session" class="form-control form-control-sm" rows="2" placeholder="Type captured session here..."></textarea>
                </li>
            </ul>
            <div class="card-body">
                <button id="unauthorized-access-btn" type="button" class="btn btn-primary">Unauthorized Access</button>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white border-0">
                <h2 class="h5 mb-1">Token Hijacking Attack</h2>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Browser A (victim)</span>
                    <span id="token-victim-status" class="fw-semibold">{{ $tokenPhish ? $tokenPhish->victim_name . ' is logged in and clicks the phishing link.' : '—' }}</span>
                </li>
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Captured token ID</span>
                    <span id="token-victim-token" class="fw-semibold text-break">{{ $tokenPhish?->victim_token ?? '—' }}</span>
                </li>
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Authentication Method</span>
                    {{-- <span class="fw-semibold">Token-Based</span> --}}
                    <span class="fw-semibold">@if($tokenPhish) Token-Based @else — @endif</span>
                </li>
                <li class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                    <span class="text-secondary">Time</span>
                    <span id="token-phish-time" class="fw-semibold">{{ $tokenPhish?->created_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
                </li>
                <li class="list-group-item py-3">
                    <textarea id="captured-token" class="form-control form-control-sm" rows="2" placeholder="Type captured token here..."></textarea>
                </li>
            </ul>
            <div class="card-body">
                <button id="unauthorized-token-btn" type="button" class="btn btn-danger">Unauthorized Access</button>
            </div>
        </div>
    </div>
</div>

<div id="attack-alert" class="alert alert-warning d-none mt-4" role="alert"></div>

<div class="d-flex justify-content-end mt-4">
    <button id="reset-asr-btn" type="button" class="btn btn-outline-secondary btn-sm">Reset</button>
</div>

<div id="attack-success-rate-section" class="card shadow-sm border-0 mt-4 d-none">
    <div class="card-header bg-white border-0">
        <h2 class="h5 mb-1">Attack Success Rate Comparison</h2>
        <p class="mb-0 small text-secondary">Each tested user contributes one result to the comparison.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-4">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Attack Type</th>
                        <th scope="col" class="text-center">Users Tested</th>
                        <th scope="col" class="text-center">Success</th>
                        <th scope="col" class="text-center">Failed</th>
                        <th scope="col" class="text-center">Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="text-primary fw-semibold">Session Hijacking</span></td>
                        <td id="session-users-tested" class="text-center">{{ $sessionAsrMetrics->pluck('victim_id')->unique()->count() }}</td>
                        <td id="session-successes" class="text-center text-success fw-semibold">0</td>
                        <td id="session-failures" class="text-center text-danger fw-semibold">0</td>
                        <td id="session-success-rate" class="text-center fw-bold">—</td>
                    </tr>
                    <tr>
                        <td><span class="text-danger fw-semibold">Token Hijacking</span></td>
                        <td id="token-users-tested" class="text-center">{{ $tokenAsrMetrics->pluck('victim_id')->unique()->count() }}</td>
                        <td id="token-successes" class="text-center text-success fw-semibold">0</td>
                        <td id="token-failures" class="text-center text-danger fw-semibold">0</td>
                        <td id="token-success-rate" class="text-center fw-bold">—</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="position: relative; height: 300px;">
            <canvas id="attackSuccessRateChart"></canvas>
        </div>
    </div>
</div>

<div id="attack-success-comparison-result-card" class="card shadow-sm border-0 mt-4 d-none">
    <div class="card-body">
        <h3 class="h5 mb-2">Comparison Result</h3>
        <p id="attack-success-comparison-result-text" class="mb-0 fw-bold fs-4"></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sessionButton = document.getElementById('unauthorized-access-btn');
        const tokenButton = document.getElementById('unauthorized-token-btn');
        const sessionInput = document.getElementById('captured-session');
        const tokenInput = document.getElementById('captured-token');
        const alertBox = document.getElementById('attack-alert');
        const section = document.getElementById('attack-success-rate-section');
        const comparisonCard = document.getElementById('attack-success-comparison-result-card');
        const comparisonText = document.getElementById('attack-success-comparison-result-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const state = {
            session: {
                usersTested: @json($sessionAsrMetrics->pluck('victim_id')->unique()->count()),
                successes: @json($sessionAsrMetrics->where('success', true)->count()),
                failed: @json($sessionAsrMetrics->where('success', false)->count()),
            },
            token: {
                usersTested: @json($tokenAsrMetrics->pluck('victim_id')->unique()->count()),
                successes: @json($tokenAsrMetrics->where('success', true)->count()),
                failed: @json($tokenAsrMetrics->where('success', false)->count()),
            },
        };
        let chart = null;

        function showAlert(message) {
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        }

        function hideAlert() {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
        }

        function rate(type) {
            const item = state[type];
            return item.usersTested ? Math.round((item.successes / item.usersTested) * 100) : null;
        }

        function updateComparison() {
            const sessionRate = rate('session');
            const tokenRate = rate('token');

            if (sessionRate === null || tokenRate === null) {
                comparisonCard.classList.add('d-none');
                return;
            }

            comparisonCard.classList.remove('d-none');
            if (sessionRate < tokenRate) {
                comparisonText.textContent = `Session is winner. Session-based authentication achieves a lower attack success rate (${sessionRate}%) than token-based authentication (${tokenRate}%).`;
                comparisonText.className = 'mb-0 fw-bold text-success fs-4';
            } else if (tokenRate < sessionRate) {
                comparisonText.textContent = `Token is winner. Token-based authentication achieves a lower attack success rate (${tokenRate}%) than session-based authentication (${sessionRate}%).`;
                comparisonText.className = 'mb-0 fw-bold text-danger fs-4';
            } else {
                comparisonText.textContent = `Both are tied. Both authentication mechanisms achieve an equal attack success rate (${sessionRate}%).`;
                comparisonText.className = 'mb-0 fw-bold text-warning fs-4';
            }
        }

        function updateTable() {
            ['session', 'token'].forEach(function (type) {
                const item = state[type];
                const label = type === 'session' ? 'session' : 'token';
                document.getElementById(`${label}-users-tested`).textContent = item.usersTested;
                document.getElementById(`${label}-successes`).textContent = item.successes;
                document.getElementById(`${label}-failures`).textContent = item.failed;
                document.getElementById(`${label}-success-rate`).textContent = rate(type) === null ? '—' : `${rate(type)}%`;
            });

            section.classList.remove('d-none');
            renderChart();
            updateComparison();
        }

        function renderChart() {
            const values = [rate('session') ?? 0, rate('token') ?? 0];
            if (!chart) {
                chart = new Chart(document.getElementById('attackSuccessRateChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Session Hijacking', 'Token Hijacking'],
                        datasets: [{
                            label: 'Attack Success Rate (%)',
                            data: values,
                            backgroundColor: ['#2563eb', '#dc3545'],
                            borderRadius: 4,
                            maxBarThickness: 120,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { stepSize: 10, callback: value => `${value}%` } },
                        },
                        plugins: { legend: { display: false } },
                    },
                });
                return;
            }

            chart.data.datasets[0].data = values;
            chart.update();
        }

        async function validate(url, payload, type) {
            hideAlert();
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok || !data.recorded) {
                    showAlert(data.message || 'Unable to record this user test.');
                    return;
                }

                state[type].usersTested = data.users_tested;
                state[type].successes = data.successes;
                state[type].failed = data.failed;
                updateTable();

                if (!data.valid) {
                    showAlert(type === 'session'
                        ? 'Session access was denied.'
                        : 'Token access was denied.');
                }
            } catch (error) {
                showAlert('Unable to validate the captured credential.');
            }
        }

        sessionButton.addEventListener('click', function () {
            const value = sessionInput.value.trim();
            if (!value) {
                showAlert('Please enter the captured session ID before continuing.');
                return;
            }
            validate('{{ route('dashboard.attack-success-rate.test', ['type' => 'session']) }}', { credential: value }, 'session');
        });

        tokenButton.addEventListener('click', function () {
            const value = tokenInput.value.trim();
            if (!value) {
                showAlert('Please enter the captured JWT token before continuing.');
                return;
            }
            validate('{{ route('dashboard.attack-success-rate.test', ['type' => 'token']) }}', { credential: value }, 'token');
        });

        document.getElementById('reset-attack-boxes-btn').addEventListener('click', async function () {
            sessionInput.value = '';
            tokenInput.value = '';

            document.getElementById('session-victim-status').textContent = '—';
            document.getElementById('session-victim-session').textContent = '—';
            document.getElementById('session-phish-time').textContent = '—';
            document.getElementById('token-victim-status').textContent = '—';
            document.getElementById('token-victim-token').textContent = '—';
            document.getElementById('token-phish-time').textContent = '—';

            try {
                await fetch('{{ route('dashboard.revocation-latency.reset-captured-credentials') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
            } catch (error) {
                showAlert('The attack cards could not be reset.');
            }

            hideAlert();
        });

        document.getElementById('reset-asr-btn').addEventListener('click', async function () {
            try {
                const response = await fetch('{{ route('dashboard.attack-success-rate.reset') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });

                if (!response.ok) {
                    showAlert('The attack success rate results could not be reset.');
                    return;
                }

                state.session = { usersTested: 0, successes: 0, failed: 0 };
                state.token = { usersTested: 0, successes: 0, failed: 0 };
                section.classList.add('d-none');
                comparisonCard.classList.add('d-none');

                if (chart) {
                    chart.data.datasets[0].data = [0, 0];
                    chart.update();
                }

                hideAlert();
            } catch (error) {
                showAlert('The attack success rate results could not be reset.');
            }
        });

        if (state.session.usersTested || state.token.usersTested) {
            updateTable();
        }
    });
</script>
@endsection
