@extends('dashboard.layout')

@section('title', 'Revocation Latency')

@section('content')
@php
    use App\Models\ExperimentMetric;

    // Latest token-based phish event (if any)
    $tokenPhish = ExperimentMetric::where('action', 'link_clicked')
        ->where('victim_authentication_type', 'token')
        ->orderBy('created_at', 'desc')
        ->first();

    // Latest session-based phish event (if any)
    $sessionPhish = ExperimentMetric::where('action', 'link_clicked')
        ->where('victim_authentication_type', 'session')
        ->orderBy('created_at', 'desc')
        ->first();

    $token_victim_name = $tokenPhish?->victim_name ?? '—';
    $token_victim_token = $tokenPhish?->victim_token ?? '—';
    $token_phish_time = $tokenPhish?->created_at?->format('Y-m-d H:i:s') ?? '—';

    $session_victim_name = $sessionPhish?->victim_name ?? '—';
    $session_victim_session = $sessionPhish?->victim_session_id ?? '—';
    $session_phish_time = $sessionPhish?->created_at?->format('Y-m-d H:i:s') ?? '—';
@endphp

<div class="mb-3">
        <h1 class="page-title">Revocation Latency</h1>
        <p class="page-copy">This page compares attack flows side by side, highlighting how token and session hijacking behave when revocation latency matters.</p>
    </div>

<div class="row row-cols-1 row-cols-lg-2 gx-4 gy-4 mt-3">
    <div class="col">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white border-0">
                <h2 class="h5 mb-1">Token Hijacking Attack</h2>
                {{-- <p class="mb-0 small opacity-75">A victim's bearer token is captured and replayed to access protected resources.</p> --}}
            </div>

            <ul class="list-group list-group-flush">
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Browser A (victim)</span>
                        <span class="fw-semibold">@if($tokenPhish) {{ $token_victim_name }} is logged in and clicks the phishing link. @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Captured token ID</span>
                        <span class="fw-semibold text-break">@if($tokenPhish) {{ $token_victim_token }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Authentication Method</span>
                        <span class="fw-semibold">@if($tokenPhish) Token-Based @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Time</span>
                        <span class="fw-semibold">@if($tokenPhish) {{ $token_phish_time }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <textarea id="captured-token" class="form-control form-control-sm" rows="2" placeholder="Type captured token here..."></textarea>
                    </div>
                </li>
            </ul>

            <div class="card-body">
                <button type="button" class="btn btn-primary">Unauthorized Access</button>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white border-0">
                <h2 class="h5 mb-1">Session Hijacking Attack</h2>
                {{-- <p class="mb-0 small opacity-75">An attacker steals a valid session ID and reuses it to impersonate the victim.</p> --}}
            </div>

            <ul class="list-group list-group-flush">
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Browser A (victim)</span>
                        <span class="fw-semibold">@if($sessionPhish) {{ $session_victim_name }} is logged in and clicks the phishing link. @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Captured session ID</span>
                        <span class="fw-semibold text-break">@if($sessionPhish) {{ $session_victim_session }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Authentication Method</span>
                        <span class="fw-semibold">@if($sessionPhish) Session-Based @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Time</span>
                        <span class="fw-semibold">@if($sessionPhish) {{ $session_phish_time }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <textarea id="captured-session" class="form-control form-control-sm" rows="2" placeholder="Type captured session here..."></textarea>
                    </div>
                </li>
            </ul>

            <div class="card-body">
                <button id="unauthorized-access-btn" type="button" class="btn btn-danger">Unauthorized Access</button>
            </div>
        </div>
    </div>
</div>

<div id="chart-alert" class="alert alert-warning alert-dismissible fade" role="alert" style="display: none;">
    <strong>Warning:</strong> Please enter the captured session ID before continuing.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div id="chart-section" class="card shadow-sm border-0 mt-4" style="display: block;">
    <div class="card-header bg-white border-0">
        <h2 class="h5 mb-1">Revocation Latency Step Line Chart</h2>
        <p class="mb-0 small opacity-75">Session-based access stays active until the victim logs out; token-based access expires after 5 minutes.</p>
    </div>
    <div class="card-body">
        <canvas id="revocationChart" height="220"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const unauthorizedButton = document.getElementById('unauthorized-access-btn');
        const sessionInput = document.getElementById('captured-session');
        const chartSection = document.getElementById('chart-section');
        const chartAlert = document.getElementById('chart-alert');
        const chartCanvas = document.getElementById('revocationChart');
        const validateSessionUrl = "{{ route('dashboard.revocation-latency.validate.session') }}";
        let revocationChart = null;
        let chartStartTime = null;
        let samplePoints = [];
        let logoutMarker = null;

        function showAlert(message) {
            chartAlert.querySelector('strong').textContent = 'Warning:';
            chartAlert.childNodes[2].textContent = ' ' + message;
            chartAlert.style.display = 'block';
            chartAlert.classList.add('show');
        }

        function hideAlert() {
            chartAlert.style.display = 'none';
            chartAlert.classList.remove('show');
        }

        function scrollToChart() {
            chartSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function ensureChartVisible() {
            chartSection.style.display = 'block';
            chartSection.classList.remove('d-none');
            if (chartCanvas) {
                chartCanvas.style.display = 'block';
                chartCanvas.style.minHeight = '220px';
            }
        }

        function buildDataPoints() {
            return samplePoints.map(function (point) {
                return { x: Number(point.x), y: Number(point.y) };
            });
        }

        function renderChart() {
            if (!chartCanvas) {
                return;
            }

            const chartData = buildDataPoints();
            const maxX = Math.max(5, Math.ceil((chartData[chartData.length - 1]?.x || 0) + 1));

            if (!revocationChart) {
                revocationChart = new Chart(chartCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: 'Access Status',
                                data: chartData,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                stepped: 'before',
                                fill: false,
                                tension: 0,
                                pointRadius: 3,
                                pointHoverRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'linear',
                                min: 0,
                                max: maxX,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        return `${value}`;
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Elapsed Time (Minutes)'
                                },
                                grid: {
                                    color: '#e5e7eb'
                                }
                            },
                            y: {
                                min: 0,
                                max: 1,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        return value === 1 ? '1 (Success / 200 OK)' : '0 (Denied / 401 Unauthorized)';
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Access Status'
                                },
                                grid: {
                                    color: '#e5e7eb'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.dataset.label || '';
                                        const value = context.parsed.y;
                                        return `${label}: ${value === 1 ? 'Success / 200 OK' : 'Denied / 401 Unauthorized'}`;
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 200
                        }
                    },
                    plugins: [{
                        id: 'logoutMarker',
                        afterDraw(chart) {
                            if (logoutMarker === null) {
                                return;
                            }

                            const xScale = chart.scales.x;
                            const ctx = chart.ctx;
                            const yTop = chart.chartArea.top;
                            const yBottom = chart.chartArea.bottom;
                            const x = xScale.getPixelForValue(logoutMarker);

                            ctx.save();
                            ctx.strokeStyle = '#d97706';
                            ctx.setLineDash([6, 4]);
                            ctx.lineWidth = 1.5;
                            ctx.beginPath();
                            ctx.moveTo(x, yTop);
                            ctx.lineTo(x, yBottom);
                            ctx.stroke();
                            ctx.setLineDash([]);

                            ctx.fillStyle = '#92400e';
                            ctx.font = '14px Inter, system-ui, sans-serif';
                            ctx.textAlign = 'center';
                            ctx.fillText(`User Logout @ ${logoutMarker.toFixed(2)}m`, x, yTop - 10);
                            ctx.restore();
                        }
                    }]
                });

                return;
            }

            revocationChart.data.datasets[0].data = chartData;
            revocationChart.options.scales.x.max = maxX;
            revocationChart.update();
        }

        function startLiveTimer() {
            chartStartTime = Date.now();
            samplePoints = [{ x: 0, y: 1 }];
            logoutMarker = null;
            ensureChartVisible();
            renderChart();
            scrollToChart();
        }

        function addSamplePoint(status = 1) {
            if (!chartStartTime) {
                return;
            }

            const elapsedMinutes = Math.min((Date.now() - chartStartTime) / 60000, 10);
            samplePoints.push({ x: elapsedMinutes, y: status });
            renderChart();
            scrollToChart();
        }

        function markLogout(logoutTimeIso) {
            if (!chartStartTime) {
                return;
            }

            const elapsedMinutes = Math.min((Date.now() - chartStartTime) / 60000, 10);
            logoutMarker = null;

            if (logoutTimeIso) {
                const logoutTimeMs = Date.parse(logoutTimeIso);
                if (!Number.isNaN(logoutTimeMs)) {
                    const exactElapsedMinutes = Math.min(Math.max((logoutTimeMs - chartStartTime) / 60000, 0), 10);
                    logoutMarker = exactElapsedMinutes;
                    samplePoints.push({ x: exactElapsedMinutes, y: 0 });
                    renderChart();
                    scrollToChart();
                    return;
                }
            }

            samplePoints.push({ x: elapsedMinutes, y: 0 });
            renderChart();
            scrollToChart();
        }

        unauthorizedButton.addEventListener('click', function () {
            const capturedValue = sessionInput.value.trim();
            if (!capturedValue) {
                showAlert('Please enter the captured session ID before continuing.');
                return;
            }

            hideAlert();

            fetch(validateSessionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ session_id: capturedValue })
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.valid) {
                        if (!chartStartTime) {
                            startLiveTimer();
                        } else {
                            addSamplePoint(1);
                        }
                    } else {
                        if (!chartStartTime) {
                            startLiveTimer();
                        }
                        if (data.logout_time) {
                            markLogout(data.logout_time);
                        } else {
                            addSamplePoint(0);
                        }
                        showAlert('Session invalidated: captured session ID is no longer valid.');
                    }
                })
                .catch(function () {
                    showAlert('Unable to validate the captured session ID.');
                });
        });

        ensureChartVisible();
        renderChart();
    });
</script>

@endsection
