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
                <button id="unauthorized-token-btn" type="button" class="btn btn-primary">Unauthorized Access</button>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white border-0">
                <h2 class="h5 mb-1">Session Hijacking Attack</h2>
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

<div id="chart-alert" class="alert alert-warning d-none mt-4" role="alert"></div>

<div id="chart-section" class="card shadow-sm border-0 mt-4 d-none">
    <div class="card-header bg-white border-0">
        <h2 class="h5 mb-1">Comparative Analysis of Access Revocation Latency (Session vs. Token)</h2>
        <p class="mb-0 small text-secondary">The chart updates only when the attacker clicks Unauthorized Access, using real logout and JWT expiration timestamps from the server.</p>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 360px;">
            <canvas id="revocationChart"></canvas>
        </div>
        <div class="border rounded p-3 mt-4 bg-light">
            <p class="mb-2"><strong>Description:</strong> Session access is invalidated immediately when the victim logs out. Token access remains valid until the JWT naturally expires (5 minutes in this demonstration), even after logout.</p>
            <p class="mb-1"><strong>Metric for Calculation:</strong> Revocation Latency (RL)</p>
            <p class="mb-1"><strong>Session RL Calculation:</strong> Time of first denied request − User Logout Time</p>
            <p class="mb-1"><strong>Token RL Calculation:</strong> Token Expiration Time − User Logout Time</p>
            <p class="mb-1" id="session-rl-result">Session RL: —</p>
            <p class="mb-0" id="token-rl-result">Token RL: —</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sessionButton = document.getElementById('unauthorized-access-btn');
        const tokenButton = document.getElementById('unauthorized-token-btn');
        const sessionInput = document.getElementById('captured-session');
        const tokenInput = document.getElementById('captured-token');
        const chartSection = document.getElementById('chart-section');
        const chartAlert = document.getElementById('chart-alert');
        const chartCanvas = document.getElementById('revocationChart');
        const sessionRlResult = document.getElementById('session-rl-result');
        const tokenRlResult = document.getElementById('token-rl-result');
        const validateSessionUrl = "{{ route('dashboard.revocation-latency.validate.session') }}";
        const validateTokenUrl = "{{ route('dashboard.revocation-latency.validate.token') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const xAxisMax = 6;

        let revocationChart = null;
        const chartMarkers = [];

        function createTracker() {
            return {
                chartStartTime: null,
                samplePoints: [],
                denialRecorded: false,
                logoutMarkerAdded: false,
                expiryMarkerAdded: false,
            };
        }

        const sessionState = createTracker();
        const tokenState = createTracker();

        function showAlert(message) {
            chartAlert.textContent = message;
            chartAlert.classList.remove('d-none');
        }

        function hideAlert() {
            chartAlert.classList.add('d-none');
            chartAlert.textContent = '';
        }

        function toMinutes(startMs, endMs) {
            return Math.min(Math.max((endMs - startMs) / 60000, 0), xAxisMax);
        }

        function getElapsedMinutes(state) {
            if (!state.chartStartTime) {
                return 0;
            }

            return toMinutes(state.chartStartTime, Date.now());
        }

        function sortPoints(state) {
            state.samplePoints.sort(function (a, b) {
                if (a.x === b.x) {
                    return b.y - a.y;
                }

                return a.x - b.x;
            });

            const deduped = [];

            state.samplePoints.forEach(function (point) {
                const last = deduped[deduped.length - 1];
                if (last && last.x === point.x && last.y === point.y) {
                    return;
                }

                deduped.push(point);
            });

            state.samplePoints = deduped;
        }

        function appendPoint(state, x, y) {
            state.samplePoints.push({
                x: Number(x.toFixed(3)),
                y: Number(y),
            });
            sortPoints(state);
        }

        function addMarker(x, label, color, type, style) {
            chartMarkers.push({
                x: Number(x.toFixed(3)),
                label: label,
                color: color || '#7c3aed',
                type: type,
                style: style || 'line',
            });
        }

        function drawMarkerAnnotation(ctx, chart, marker) {
            const xScale = chart.scales.x;
            const yScale = chart.scales.y;
            const x = xScale.getPixelForValue(marker.x);
            const ySuccess = yScale.getPixelForValue(1);
            const yTop = chart.chartArea.top;
            const yBottom = chart.chartArea.bottom;

            ctx.save();
            ctx.strokeStyle = marker.color;
            ctx.setLineDash([6, 4]);
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(x, yTop);
            ctx.lineTo(x, yBottom);
            ctx.stroke();
            ctx.setLineDash([]);

            if (marker.style === 'callout') {
                const lines = marker.label.split(' — ');
                const title = lines[0] || marker.label;
                const subtitle = lines.slice(1).join(' — ');
                const paddingX = 8;
                const lineHeight = 14;
                ctx.font = 'bold 11px Inter, system-ui, sans-serif';
                const titleWidth = ctx.measureText(title).width;
                ctx.font = '11px Inter, system-ui, sans-serif';
                const subtitleWidth = subtitle ? ctx.measureText(subtitle).width : 0;
                const boxWidth = Math.max(titleWidth, subtitleWidth) + (paddingX * 2);
                const boxHeight = subtitle ? 42 : 28;
                const boxX = Math.min(
                    Math.max(x - (boxWidth / 2), chart.chartArea.left + 4),
                    chart.chartArea.right - boxWidth - 4
                );
                const boxY = Math.max(yTop + 6, ySuccess - boxHeight - 18);

                ctx.fillStyle = marker.color === '#dc2626' ? '#fef2f2' : '#f5f3ff';
                ctx.strokeStyle = marker.color;
                ctx.lineWidth = 1.25;
                ctx.beginPath();
                if (typeof ctx.roundRect === 'function') {
                    ctx.roundRect(boxX, boxY, boxWidth, boxHeight, 6);
                } else {
                    ctx.rect(boxX, boxY, boxWidth, boxHeight);
                }
                ctx.fill();
                ctx.stroke();

                ctx.beginPath();
                ctx.moveTo(x, boxY + boxHeight);
                ctx.lineTo(x, Math.min(ySuccess - 4, yBottom - 8));
                ctx.stroke();

                ctx.fillStyle = marker.color === '#dc2626' ? '#991b1b' : '#5b21b6';
                ctx.font = 'bold 11px Inter, system-ui, sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(title, boxX + paddingX, boxY + 14);

                if (subtitle) {
                    ctx.font = '11px Inter, system-ui, sans-serif';
                    ctx.fillText(subtitle, boxX + paddingX, boxY + 14 + lineHeight);
                }
            } else {
                ctx.fillStyle = marker.color;
                ctx.font = '11px Inter, system-ui, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(marker.label, x, yTop - 8);
            }

            ctx.restore();
        }

        function clearMarkers(type) {
            for (let index = chartMarkers.length - 1; index >= 0; index -= 1) {
                if (chartMarkers[index].type === type) {
                    chartMarkers.splice(index, 1);
                }
            }
        }

        function ensureChartVisible() {
            chartSection.classList.remove('d-none');
        }

        function scrollToChart() {
            chartSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function formatLatency(seconds) {
            if (seconds === null || seconds === undefined) {
                return '—';
            }

            if (seconds === 0) {
                return '0 seconds (Immediate)';
            }

            if (seconds < 60) {
                return `${seconds} second${seconds === 1 ? '' : 's'}`;
            }

            const minutes = (seconds / 60).toFixed(2);
            return `${minutes} minutes (${seconds} seconds)`;
        }

        function updateSessionRL(data, denialTimeMs) {
            if (data.revocation_latency_seconds !== null && data.revocation_latency_seconds !== undefined) {
                sessionRlResult.textContent = `Session RL: ${formatLatency(data.revocation_latency_seconds)}`;
                return;
            }

            if (data.logout_time) {
                const latencySeconds = Math.max(0, Math.round((denialTimeMs - Date.parse(data.logout_time)) / 1000));
                sessionRlResult.textContent = `Session RL: ${formatLatency(latencySeconds)}`;
            }
        }

        function updateTokenRL(data) {
            if (data.revocation_latency_seconds !== null && data.revocation_latency_seconds !== undefined) {
                tokenRlResult.textContent = `Token RL: ${formatLatency(data.revocation_latency_seconds)}`;
                return;
            }

            if (data.logout_time && data.token_expiration_time) {
                const latencySeconds = Math.max(0, Math.round(
                    (Date.parse(data.token_expiration_time) - Date.parse(data.logout_time)) / 1000
                ));
                tokenRlResult.textContent = `Token RL: ${formatLatency(latencySeconds)}`;
            }
        }

        function isTokenAccessValid(data) {
            if (data.expired) {
                return false;
            }

            if (data.valid) {
                return true;
            }

            if (data.token_expiration_time && Date.parse(data.token_expiration_time) > Date.now()) {
                return true;
            }

            return Boolean(data.logout_occurred && data.expired === false);
        }

        function tokenStillValidAfterLogout(data) {
            return Boolean(data.logout_time && data.token_expiration_time && data.expired === false);
        }

        function buildDataset(state, label, color, backgroundColor) {
            return {
                label: label,
                data: state.samplePoints.map(function (point) {
                    return { x: point.x, y: point.y };
                }),
                borderColor: color,
                backgroundColor: backgroundColor,
                stepped: 'after',
                fill: false,
                tension: 0,
                pointRadius: state.samplePoints.length ? 4 : 0,
                pointHoverRadius: 5,
                pointBackgroundColor: color,
            };
        }

        function renderChart() {
            if (!chartCanvas) {
                return;
            }

            const datasets = [
                buildDataset(sessionState, 'Session (Immediate Revocation)', '#2563eb', 'rgba(37, 99, 235, 0.08)'),
                buildDataset(tokenState, 'Token (Expires After 5 Minutes)', '#dc3545', 'rgba(220, 53, 69, 0.08)'),
            ];

            if (!revocationChart) {
                revocationChart = new Chart(chartCanvas.getContext('2d'), {
                    type: 'line',
                    data: { datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'linear',
                                min: 0,
                                max: xAxisMax,
                                ticks: { stepSize: 1 },
                                title: {
                                    display: true,
                                    text: 'Time (Minutes)',
                                },
                                grid: { color: '#e5e7eb' },
                            },
                            y: {
                                min: 0,
                                max: 1,
                                ticks: {
                                    stepSize: 1,
                                    callback: function (value) {
                                        return value === 1
                                            ? '1 (Success / 200 OK)'
                                            : '0 (Access Denied / 401 Unauthorized)';
                                    },
                                },
                                title: {
                                    display: true,
                                    text: 'Access Status',
                                },
                                grid: { color: '#e5e7eb' },
                            },
                        },
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = context.parsed.y;
                                        return value === 1
                                            ? 'Success / 200 OK'
                                            : 'Access Denied / 401 Unauthorized';
                                    },
                                },
                            },
                        },
                        animation: { duration: 250 },
                    },
                    plugins: [{
                        id: 'eventMarkers',
                        afterDraw(chart) {
                            if (!chartMarkers.length) {
                                return;
                            }

                            chartMarkers.forEach(function (marker) {
                                drawMarkerAnnotation(chart.ctx, chart, marker);
                            });
                        },
                    }],
                });

                return;
            }

            revocationChart.data.datasets = datasets;
            revocationChart.update();
        }

        function startTest(state, type) {
            clearMarkers(type);
            state.chartStartTime = Date.now();
            state.samplePoints = [{ x: 0, y: 1 }];
            state.denialRecorded = false;
            state.logoutMarkerAdded = false;
            state.expiryMarkerAdded = false;
            ensureChartVisible();
            renderChart();
            scrollToChart();
        }

        function recordSuccessfulAccess(state, data, type, options) {
            const elapsedMinutes = getElapsedMinutes(state);
            const logoutDetected = Boolean(data.logout_time || data.logout_occurred);

            if (options?.onLogoutWhileValid && logoutDetected && !state.logoutMarkerAdded) {
                const logoutMinute = data.logout_time
                    ? toMinutes(state.chartStartTime, Date.parse(data.logout_time))
                    : elapsedMinutes;

                addMarker(
                    logoutMinute,
                    options.logoutLabel,
                    options.markerColor || '#7c3aed',
                    type,
                    options.markerStyle || 'callout'
                );
                state.logoutMarkerAdded = true;
                appendPoint(state, logoutMinute, 1);

                if (options.updateRl) {
                    options.updateRl(data);
                }
            }

            appendPoint(state, elapsedMinutes, 1);
            renderChart();
            scrollToChart();
        }

        function recordSessionDeniedAccess(data) {
            if (sessionState.denialRecorded) {
                appendPoint(sessionState, getElapsedMinutes(sessionState), 0);
                renderChart();
                scrollToChart();
                return;
            }

            sessionState.denialRecorded = true;
            const denialTimeMs = Date.now();
            const denialMinute = toMinutes(sessionState.chartStartTime, denialTimeMs);

            if (data.logout_time) {
                const logoutMinute = toMinutes(sessionState.chartStartTime, Date.parse(data.logout_time));
                addMarker(logoutMinute, 'Event: User Logout', '#7c3aed', 'session');
                sessionState.logoutMarkerAdded = true;
                appendPoint(sessionState, logoutMinute, 0);

                if (denialMinute > logoutMinute) {
                    appendPoint(sessionState, denialMinute, 0);
                }
            } else {
                appendPoint(sessionState, denialMinute, 0);
            }

            updateSessionRL(data, denialTimeMs);
            renderChart();
            scrollToChart();
        }

        function recordTokenExpiredAccess(data) {
            if (tokenState.expiryRecorded) {
                appendPoint(tokenState, getElapsedMinutes(tokenState), 0);
                renderChart();
                scrollToChart();
                return;
            }

            tokenState.expiryRecorded = true;
            tokenState.denialRecorded = true;

            const denialTimeMs = Date.now();
            const denialMinute = toMinutes(tokenState.chartStartTime, denialTimeMs);
            const expirationMs = data.token_expiration_time ? Date.parse(data.token_expiration_time) : null;
            const expirationMinute = expirationMs
                ? toMinutes(tokenState.chartStartTime, expirationMs)
                : Math.min(5, denialMinute);

            if (!tokenState.expiryMarkerAdded) {
                addMarker(expirationMinute, 'Event: Token Expired', '#dc2626', 'token', 'callout');
                tokenState.expiryMarkerAdded = true;
            }

            appendPoint(tokenState, expirationMinute, 0);

            if (denialMinute > expirationMinute) {
                appendPoint(tokenState, denialMinute, 0);
            }

            updateTokenRL(data);
            renderChart();
            scrollToChart();
        }

        async function postJson(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            return response.json();
        }

        sessionButton.addEventListener('click', async function () {
            const capturedValue = sessionInput.value.trim();

            if (!capturedValue) {
                showAlert('Please enter the captured session ID before continuing.');
                return;
            }

            hideAlert();

            try {
                const data = await postJson(validateSessionUrl, { session_id: capturedValue });

                if (data.valid) {
                    if (!sessionState.chartStartTime) {
                        startTest(sessionState, 'session');
                    } else {
                        recordSuccessfulAccess(sessionState, data, 'session');
                    }

                    return;
                }

                if (!sessionState.chartStartTime) {
                    showAlert('Captured session ID is not valid. Start with a live captured session before the victim logs out.');
                    return;
                }

                recordSessionDeniedAccess(data);
                showAlert('Session invalidated: captured session ID is no longer valid.');
            } catch (error) {
                showAlert('Unable to validate the captured session ID.');
            }
        });

        tokenButton.addEventListener('click', async function () {
            const capturedValue = tokenInput.value.trim();

            if (!capturedValue) {
                showAlert('Please enter the captured JWT token before continuing.');
                return;
            }

            hideAlert();

            try {
                const data = await postJson(validateTokenUrl, { token: capturedValue });

                if (isTokenAccessValid(data)) {
                    if (!tokenState.chartStartTime) {
                        startTest(tokenState, 'token');
                        tokenRlResult.textContent = 'Token RL: —';
                    }

                    recordSuccessfulAccess(tokenState, data, 'token', {
                        onLogoutWhileValid: true,
                        logoutLabel: 'Event: User Logout — JWT Still Valid',
                        markerStyle: 'callout',
                        updateRl: updateTokenRL,
                    });

                    return;
                }

                if (!tokenState.chartStartTime) {
                    showAlert('Captured JWT token is not valid. Log in via token authentication and capture a fresh JWT before testing.');
                    return;
                }

                if (data.expired) {
                    recordTokenExpiredAccess(data);
                    showAlert('JWT expired: captured token is no longer valid.');
                    return;
                }

                appendPoint(tokenState, getElapsedMinutes(tokenState), 0);
                renderChart();
                scrollToChart();
                showAlert('Captured JWT token is no longer valid.');
            } catch (error) {
                showAlert('Unable to validate the captured JWT token.');
            }
        });
    });
</script>

@endsection
