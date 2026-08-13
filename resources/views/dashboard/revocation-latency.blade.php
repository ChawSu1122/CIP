@extends('dashboard.layout')

@section('title', 'Revocation Latency')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
<style>
    .rl-metric-formula-box {
        width: fit-content;
        margin: 0.5rem auto;
        padding: 0.75rem 1.5rem;
        background: #fff;
        border: 1px solid #000;
        border-radius: 0.25rem;
    }

    .rl-metric-formula-box .katex {
        color: #000;
        font-size: 1.25rem;
    }
</style>
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
    $token_phish_ts = $tokenPhish?->created_at?->valueOf() ?? null;

    $session_victim_name = $sessionPhish?->victim_name ?? '—';
    $session_victim_session = $sessionPhish?->victim_session_id ?? '—';
    $session_phish_time = $sessionPhish?->created_at?->format('Y-m-d H:i:s') ?? '—';
    $session_phish_ts = $sessionPhish?->created_at?->valueOf() ?? null;
@endphp

<div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-3">
    <div>
        <h1 class="page-title">Revocation Latency</h1>
        <p class="page-copy">This page compares attack flows side by side, highlighting how token and session hijacking behave when revocation latency matters.</p>
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
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Browser A (victim)</span>
                        <span id="session-victim-status" class="fw-semibold">@if($sessionPhish) {{ $session_victim_name }} is logged in and clicks the phishing link. @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Captured session ID</span>
                        <span id="session-victim-session" class="fw-semibold text-break">@if($sessionPhish) {{ $session_victim_session }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Authentication Method</span>
                        <span id="session-auth-method" class="fw-semibold">@if($sessionPhish) Session-Based @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Time</span>
                        <span id="session-phish-time" class="fw-semibold">@if($sessionPhish) {{ $session_phish_time }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <textarea id="captured-session" class="form-control form-control-sm" rows="2" placeholder="Type captured session here..."></textarea>
                    </div>
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
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Browser A (victim)</span>
                        <span id="token-victim-status" class="fw-semibold">@if($tokenPhish) {{ $token_victim_name }} is logged in and clicks the phishing link. @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Captured token ID</span>
                        <span id="token-victim-token" class="fw-semibold text-break">@if($tokenPhish) {{ $token_victim_token }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Authentication Method</span>
                        <span id="token-auth-method" class="fw-semibold">@if($tokenPhish) Token-Based @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-secondary">Time</span>
                        <span id="token-phish-time" class="fw-semibold">@if($tokenPhish) {{ $token_phish_time }} @else — @endif</span>
                    </div>
                </li>

                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <textarea id="captured-token" class="form-control form-control-sm" rows="2" placeholder="Type captured token here..."></textarea>
                    </div>
                </li>
            </ul>

            <div class="card-body">
                <button id="unauthorized-token-btn" type="button" class="btn btn-danger">Unauthorized Access</button>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <button id="reset-chart-btn" type="button" class="btn btn-outline-secondary btn-sm">Reset</button>
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
            <div class="rl-metric-formula-box">
                <span id="rl-metric-formula"></span>
            </div>
            <p class="mb-1"><strong>Definition:</strong> Revocation latency is the time between a decision to remove access and the point at which that access is actually gone.</p>
            <p class="mb-1"><strong>Session RL Calculation:</strong> Access Invalidated Time − Revocation Time</p>
            <p class="mb-1 fs-5 fw-bold" id="session-rl-result">Session RL Calculation:</p>
            <p class="mb-1"><strong>Token RL Calculation:</strong> Access Invalidated Time − Revocation Time</p>
            <p class="mb-0 fs-5 fw-bold" id="token-rl-result">Token RL Calculation:</p>
        </div>
    </div>
</div>

<div id="comparison-result-card" class="card shadow-sm border-0 mt-4 d-none">
    <div class="card-body">
        <h3 class="h5 mb-2">Comparison Result</h3>
        <p id="comparison-result-text" class="mb-0 fw-bold text-success fs-4">
            Session is winner because Revocation Latency of Session is less than Revocation Latency of Token.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    katex.render('RL = T_{\\text{Access Invalid}} - T_{\\text{Revocation}}', document.getElementById('rl-metric-formula'), {
        displayMode: true,
        throwOnError: false,
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sessionButton = document.getElementById('unauthorized-access-btn');
        const tokenButton = document.getElementById('unauthorized-token-btn');
        const sessionInput = document.getElementById('captured-session');
        const tokenInput = document.getElementById('captured-token');
        const tokenVictimStatus = document.getElementById('token-victim-status');
        const tokenVictimToken = document.getElementById('token-victim-token');
        const tokenAuthMethod = document.getElementById('token-auth-method');
        const tokenPhishTime = document.getElementById('token-phish-time');
        const sessionVictimStatus = document.getElementById('session-victim-status');
        const sessionVictimSession = document.getElementById('session-victim-session');
        const sessionAuthMethod = document.getElementById('session-auth-method');
        const sessionPhishTime = document.getElementById('session-phish-time');
        const chartSection = document.getElementById('chart-section');

        document.cookie = 'revocation_latency_reset=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
        const chartAlert = document.getElementById('chart-alert');
        const chartCanvas = document.getElementById('revocationChart');
        const sessionRlResult = document.getElementById('session-rl-result');
        const tokenRlResult = document.getElementById('token-rl-result');
        const comparisonResultCard = document.getElementById('comparison-result-card');
        const comparisonResultText = document.getElementById('comparison-result-text');
        const resetAttackBoxesButton = document.getElementById('reset-attack-boxes-btn');
        const resetChartButton = document.getElementById('reset-chart-btn');
        const resetCookieName = 'revocation_latency_reset';
        const validateSessionUrl = "{{ route('dashboard.revocation-latency.validate.session') }}";
        const validateTokenUrl = "{{ route('dashboard.revocation-latency.validate.token') }}";
        const resetCapturedCredentialsUrl = "{{ route('dashboard.revocation-latency.reset-captured-credentials') }}";
        const securityAlertUrl = "{{ route('dashboard.revocation-latency.security-alert') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const xAxisMax = 6;
        const tokenTtlSeconds = 300;
        const tokenExpiryChartMinute = tokenTtlSeconds / 60;
        const sessionLogoutEventKey = 'victim-logout-event-session';
        const tokenLogoutEventKey = 'victim-logout-event-token';
        const resetMarkerKey = 'revocation-latency-attack-reset';
        const chartStateKey = 'revocation-latency-chart-state';
        const tokenPhishTs = @json($token_phish_ts);
        const sessionPhishTs = @json($session_phish_ts);

        let revocationChart = null;
        const chartMarkers = [];
        let sessionLogoutTimeMs = null;
        let tokenLogoutTimeMs = null;
        let sessionAccessInvalidMs = null;
        let tokenAccessInvalidMs = null;
        let lastTokenValidation = null;

        function syncVictimLogoutTimes() {
            const sessionTs = Number(window.localStorage.getItem(sessionLogoutEventKey) || '0');
            const tokenTs = Number(window.localStorage.getItem(tokenLogoutEventKey) || '0');
            sessionLogoutTimeMs = sessionTs > 0 ? sessionTs : null;
            tokenLogoutTimeMs = tokenTs > 0 ? tokenTs : null;
        }

        function applyServerLogoutTime(data, type) {
            if (!data.logout_time) {
                return;
            }

            const ms = Date.parse(data.logout_time);
            if (Number.isNaN(ms)) {
                return;
            }

            if (type === 'session') {
                sessionLogoutTimeMs = ms;
                window.localStorage.setItem(sessionLogoutEventKey, String(ms));
                refreshLogoutMarkerPosition('session');
                renderSessionRL();
            } else {
                tokenLogoutTimeMs = ms;
                window.localStorage.setItem(tokenLogoutEventKey, String(ms));
                refreshLogoutMarkerPosition('token');
                renderTokenRL();
            }

            if (revocationChart) {
                renderChart();
            }
        }

        function resolveChartStartTime(type, data, phishTs) {
            if (phishTs && phishTs > 0) {
                return phishTs;
            }

            if (type === 'token') {
                const issuedAtMs = data.token_issued_at ? Date.parse(data.token_issued_at) : null;
                if (issuedAtMs && !Number.isNaN(issuedAtMs)) {
                    return issuedAtMs;
                }
            }

            return Date.now();
        }

        syncVictimLogoutTimes();

        window.addEventListener('victim-logout', function (event) {
            const type = event.detail?.type === 'token' ? 'token' : 'session';
            syncVictimLogoutTimes();

            if (type === 'session') {
                handleSessionVictimLogout();
            } else {
                handleTokenVictimLogout();
            }
        });

        window.addEventListener('storage', function (event) {
            if (event.key === sessionLogoutEventKey) {
                syncVictimLogoutTimes();
                handleSessionVictimLogout();
            }

            if (event.key === tokenLogoutEventKey) {
                syncVictimLogoutTimes();
                handleTokenVictimLogout();
            }
        });

        function createTracker() {
            return {
                chartStartTime: null,
                samplePoints: [],
                denialRecorded: false,
                logoutMarkerAdded: false,
                expiryMarkerAdded: false,
                expiryRecorded: false,
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

        function toChartMinutes(startMs, endMs) {
            if (!startMs || !endMs) {
                return 0;
            }

            const elapsedSeconds = Math.max(0, Math.round((endMs - startMs) / 1000));
            return Math.min(elapsedSeconds / 60, xAxisMax);
        }

        function getElapsedMinutes(state) {
            if (!state.chartStartTime) {
                return 0;
            }

            return toChartMinutes(state.chartStartTime, Date.now());
        }

        function refreshLogoutMarkerPosition(type) {
            const state = type === 'session' ? sessionState : tokenState;
            const logoutMs = type === 'session' ? sessionLogoutTimeMs : tokenLogoutTimeMs;

            if (!state.chartStartTime || !logoutMs || !state.logoutMarkerAdded) {
                return;
            }

            const x = toChartMinutes(state.chartStartTime, logoutMs);

            chartMarkers.forEach(function (marker) {
                if (marker.type === type && marker.label.includes('Logout')) {
                    marker.x = Number(x.toFixed(3));
                }
            });
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

        function appendPoint(state, x, y, options) {
            state.samplePoints.push({
                x: Number(x.toFixed(3)),
                y: Number(y),
                hidden: Boolean(options?.hidden),
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

        function saveChartState() {
            const snapshot = {
                visible: !chartSection.classList.contains('d-none'),
                session: {
                    chartStartTime: sessionState.chartStartTime,
                    samplePoints: sessionState.samplePoints,
                    denialRecorded: sessionState.denialRecorded,
                    logoutMarkerAdded: sessionState.logoutMarkerAdded,
                    expiryMarkerAdded: sessionState.expiryMarkerAdded,
                    expiryRecorded: sessionState.expiryRecorded,
                },
                token: {
                    chartStartTime: tokenState.chartStartTime,
                    samplePoints: tokenState.samplePoints,
                    denialRecorded: tokenState.denialRecorded,
                    logoutMarkerAdded: tokenState.logoutMarkerAdded,
                    expiryMarkerAdded: tokenState.expiryMarkerAdded,
                    expiryRecorded: tokenState.expiryRecorded,
                },
                markers: chartMarkers,
                sessionLogoutTimeMs: sessionLogoutTimeMs,
                tokenLogoutTimeMs: tokenLogoutTimeMs,
                sessionAccessInvalidMs: sessionAccessInvalidMs,
                tokenAccessInvalidMs: tokenAccessInvalidMs,
                sessionRl: sessionRlResult.textContent,
                tokenRl: tokenRlResult.textContent,
            };

            window.localStorage.setItem(chartStateKey, JSON.stringify(snapshot));
        }

        function restoreChartState() {
            const saved = window.localStorage.getItem(chartStateKey);

            if (!saved) {
                return;
            }

            try {
                const snapshot = JSON.parse(saved);

                if (!snapshot) {
                    return;
                }

                if (snapshot.session) {
                    Object.assign(sessionState, snapshot.session);
                }

                if (snapshot.token) {
                    Object.assign(tokenState, snapshot.token);
                }

                if (Array.isArray(snapshot.markers)) {
                    chartMarkers.length = 0;
                    snapshot.markers.forEach(function (marker) {
                        if (marker.type === 'token' && marker.color === '#dc2626') {
                            marker.x = tokenExpiryChartMinute;
                            marker.label = 'Token Expired';
                            marker.style = 'callout';
                        }

                        chartMarkers.push({ ...marker });
                    });
                }

                if (snapshot.sessionLogoutTimeMs) {
                    sessionLogoutTimeMs = snapshot.sessionLogoutTimeMs;
                }

                if (snapshot.tokenLogoutTimeMs) {
                    tokenLogoutTimeMs = snapshot.tokenLogoutTimeMs;
                }

                if (snapshot.sessionAccessInvalidMs) {
                    sessionAccessInvalidMs = snapshot.sessionAccessInvalidMs;
                }

                if (snapshot.tokenAccessInvalidMs) {
                    tokenAccessInvalidMs = snapshot.tokenAccessInvalidMs;
                }

                renderSessionRL();
                renderTokenRL();

                if (snapshot.visible || sessionState.chartStartTime || tokenState.chartStartTime) {
                    ensureChartVisible();
                    renderChart();
                }
            } catch (error) {
                window.localStorage.removeItem(chartStateKey);
            }
        }

        function clearResetCookie() {
            document.cookie = `${resetCookieName}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax`;
        }

        async function sendSecurityAlertToServer(type, payload) {
            try {
                await fetch(securityAlertUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        type,
                        payload,
                        message: 'Someone is trying to use your account. So if it is not you, please logout of all devices',
                    }),
                });
            } catch (error) {
                console.error('Security alert failed to persist.', error);
            }
        }

        function resetStoredLogoutMarkers() {
            window.localStorage.removeItem(sessionLogoutEventKey);
            window.localStorage.removeItem(tokenLogoutEventKey);
            sessionLogoutTimeMs = null;
            tokenLogoutTimeMs = null;
        }

        function resetAttackBoxes() {
            window.localStorage.setItem(resetMarkerKey, String(Date.now()));
            resetStoredLogoutMarkers();

            sessionInput.value = '';
            tokenInput.value = '';
            sessionInput.dispatchEvent(new Event('input', { bubbles: true }));
            tokenInput.dispatchEvent(new Event('input', { bubbles: true }));

            if (tokenVictimStatus) tokenVictimStatus.textContent = '—';
            if (tokenVictimToken) tokenVictimToken.textContent = '—';
            if (tokenAuthMethod) tokenAuthMethod.textContent = '—';
            if (tokenPhishTime) tokenPhishTime.textContent = '—';

            if (sessionVictimStatus) sessionVictimStatus.textContent = '—';
            if (sessionVictimSession) sessionVictimSession.textContent = '—';
            if (sessionAuthMethod) sessionAuthMethod.textContent = '—';
            if (sessionPhishTime) sessionPhishTime.textContent = '—';

            clearResetCookie();
            hideAlert();
        }

        function shouldSuppressAttackBoxes() {
            const resetTs = Number(window.localStorage.getItem(resetMarkerKey) || '0');

            if (!resetTs) {
                return false;
            }

            // If a new phish event happened after the reset, show the fresh data again.
            if ((tokenPhishTs && tokenPhishTs > resetTs) || (sessionPhishTs && sessionPhishTs > resetTs)) {
                return false;
            }

            return true;
        }

        // Keep the attack boxes cleared on page refresh until a new phish event arrives.
        if (shouldSuppressAttackBoxes()) {
            resetAttackBoxes();
        }

        restoreChartState();

        function resetChartData() {
            chartMarkers.length = 0;
            resetStoredLogoutMarkers();
            Object.assign(sessionState, createTracker());
            Object.assign(tokenState, createTracker());
            sessionAccessInvalidMs = null;
            tokenAccessInvalidMs = null;
            renderSessionRL();
            renderTokenRL();
            window.localStorage.removeItem(chartStateKey);
            hideAlert();

            if (revocationChart) {
                revocationChart.data.datasets = [
                    buildDataset(sessionState, 'Session (Immediate Revocation)', '#2563eb', 'rgba(37, 99, 235, 0.08)'),
                    buildDataset(tokenState, 'Token (Expires After 5 Minutes)', '#dc3545', 'rgba(220, 53, 69, 0.08)'),
                ];
                revocationChart.update();
            } else {
                ensureChartVisible();
                renderChart();
            }
        }

        function scrollToChart() {
            chartSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function formatDuration(totalSeconds) {
            if (totalSeconds === 0) {
                return '0 seconds (Immediate)';
            }

            if (totalSeconds < 60) {
                return `${totalSeconds} second${totalSeconds === 1 ? '' : 's'}`;
            }

            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;

            if (seconds === 0) {
                return `${minutes} min`;
            }

            return `${minutes} min ${seconds} second${seconds === 1 ? '' : 's'}`;
        }

        function formatElapsedFromStart(startMs, eventMs) {
            if (!startMs || !eventMs) {
                return '—';
            }

            const totalSeconds = Math.max(0, Math.round((eventMs - startMs) / 1000));
            return formatDuration(totalSeconds);
        }

        function calculateSessionLatencySeconds() {
            if (!sessionLogoutTimeMs || !sessionAccessInvalidMs) {
                return null;
            }

            return Math.max(0, Math.round((sessionAccessInvalidMs - sessionLogoutTimeMs) / 1000));
        }

        function calculateTokenLatencySeconds() {
            if (!tokenLogoutTimeMs || !tokenState.chartStartTime || !tokenState.expiryRecorded) {
                return null;
            }

            const revocationSeconds = Math.max(0, Math.round((tokenLogoutTimeMs - tokenState.chartStartTime) / 1000));
            return Math.max(0, tokenTtlSeconds - revocationSeconds);
        }

        function updateComparisonResult() {
            if (!comparisonResultText || !comparisonResultCard) {
                return;
            }

            const sessionLatency = calculateSessionLatencySeconds();
            const tokenLatency = calculateTokenLatencySeconds();

            if (sessionLatency === null || tokenLatency === null) {
                comparisonResultCard.classList.add('d-none');
                return;
            }

            comparisonResultCard.classList.remove('d-none');

            if (sessionLatency < tokenLatency) {
                comparisonResultText.textContent = 'Session is winner because Revocation Latency of Session is less than Revocation Latency of Token.';
                comparisonResultText.className = 'mb-0 fw-bold text-success fs-4';
                return;
            }

            if (tokenLatency < sessionLatency) {
                comparisonResultText.textContent = 'Token is winner because Revocation Latency of Token is less than Revocation Latency of Session.';
                comparisonResultText.className = 'mb-0 fw-bold text-danger fs-4';
                return;
            }

            comparisonResultText.textContent = 'The comparison is tied because both Revocation Latency values are equal.';
            comparisonResultText.className = 'mb-0 fw-bold text-warning fs-4';
        }

        function renderSessionRL() {
            const startMs = sessionState.chartStartTime;
            const revocationMs = sessionLogoutTimeMs;

            if (!startMs || !revocationMs) {
                sessionRlResult.textContent = 'Session RL Calculation:';
                sessionRlResult.className = 'mb-1 fs-5 fw-bold';
                updateComparisonResult();
                return;
            }

            const accessInvalidLabel = sessionAccessInvalidMs
                ? formatElapsedFromStart(startMs, sessionAccessInvalidMs)
                : '—';
            const revocationLabel = formatElapsedFromStart(startMs, revocationMs);
            let resultLabel = '—';

            if (sessionAccessInvalidMs) {
                const latencySeconds = Math.max(0, Math.round((sessionAccessInvalidMs - revocationMs) / 1000));
                resultLabel = formatDuration(latencySeconds);
            }

            sessionRlResult.textContent = `Session RL Calculation: ${accessInvalidLabel} − ${revocationLabel} = ${resultLabel}`;
            sessionRlResult.className = 'mb-1 fs-5 fw-bold';
            updateComparisonResult();
        }

        function renderTokenRL() {
            const startMs = tokenState.chartStartTime;
            const revocationMs = tokenLogoutTimeMs;

            if (!startMs || !revocationMs || !tokenState.expiryRecorded) {
                tokenRlResult.textContent = 'Token RL Calculation:';
                tokenRlResult.className = 'mb-0 fs-5 fw-bold';
                updateComparisonResult();
                return;
            }

            const accessInvalidLabel = formatDuration(tokenTtlSeconds);
            const revocationLabel = formatElapsedFromStart(startMs, revocationMs);
            const revocationSeconds = Math.max(0, Math.round((revocationMs - startMs) / 1000));
            const latencySeconds = Math.max(0, tokenTtlSeconds - revocationSeconds);
            const resultLabel = formatDuration(latencySeconds);

            tokenRlResult.textContent = `Token RL Calculation: ${accessInvalidLabel} − ${revocationLabel} = ${resultLabel}`;
            tokenRlResult.className = 'mb-0 fs-5 fw-bold';
            updateComparisonResult();
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
                    return { x: point.x, y: point.y, hidden: point.hidden };
                }),
                borderColor: color,
                backgroundColor: backgroundColor,
                fill: false,
                tension: 0,
                pointRadius: function (context) {
                    const point = context.dataset.data[context.dataIndex];
                    if (!point || point.x === 0 || point.hidden) {
                        return 0;
                    }

                    return 4;
                },
                pointHoverRadius: function (context) {
                    const point = context.dataset.data[context.dataIndex];
                    if (!point || point.x === 0 || point.hidden) {
                        return 0;
                    }

                    return 5;
                },
                pointHitRadius: function (context) {
                    const point = context.dataset.data[context.dataIndex];
                    if (!point || point.x === 0 || point.hidden) {
                        return 0;
                    }

                    return 4;
                },
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
                                            ? 'Success / 200 OK'
                                            : 'Access Denied / 401 Unauthorized';
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
                                    title: function (tooltipItems) {
                                        const minutes = tooltipItems[0]?.parsed?.x;
                                        if (minutes === undefined || minutes === null) {
                                            return '';
                                        }

                                        const totalSeconds = Math.round(minutes * 60);
                                        return `${formatDuration(totalSeconds)} from attack start`;
                                    },
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

        function startTest(state, type, startTimeMs) {
            clearMarkers(type);
            if (type === 'session') {
                window.localStorage.removeItem(sessionLogoutEventKey);
                sessionLogoutTimeMs = null;
                sessionAccessInvalidMs = null;
            }
            if (type === 'token') {
                window.localStorage.removeItem(tokenLogoutEventKey);
                tokenLogoutTimeMs = null;
                tokenAccessInvalidMs = null;
            }
            state.chartStartTime = startTimeMs || Date.now();
            state.samplePoints = [{ x: 0, y: 1 }];
            state.denialRecorded = false;
            state.logoutMarkerAdded = false;
            state.expiryMarkerAdded = false;
            state.expiryRecorded = false;
            state.unauthorizedAttempts = 0;
            ensureChartVisible();
            renderChart();
            if (type === 'session') {
                renderSessionRL();
            } else {
                renderTokenRL();
            }
            saveChartState();
            scrollToChart();
        }

        function recordSuccessfulAccess(state, data, type, options) {
            const elapsedMinutes = getElapsedMinutes(state);
            const logoutTimestampMs = options?.logoutTimeMs || (data.logout_time ? Date.parse(data.logout_time) : null);
            const logoutDetected = Boolean(logoutTimestampMs || data.logout_time || data.logout_occurred);

            if (options?.onLogoutWhileValid && logoutDetected && !state.logoutMarkerAdded) {
                const logoutMinute = logoutTimestampMs
                    ? toChartMinutes(state.chartStartTime, logoutTimestampMs)
                    : (data.logout_time ? toChartMinutes(state.chartStartTime, Date.parse(data.logout_time)) : elapsedMinutes);

                addMarker(
                    logoutMinute,
                    options.logoutLabel,
                    options.markerColor || '#7c3aed',
                    type,
                    options.markerStyle || 'callout'
                );
                state.logoutMarkerAdded = true;

                if (options.updateRl) {
                    options.updateRl();
                }
            }

            appendPoint(state, elapsedMinutes, 1);
            renderChart();
            saveChartState();
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
            const denialMinute = toChartMinutes(sessionState.chartStartTime, denialTimeMs);
            const logoutTimeMs = data.logout_time ? Date.parse(data.logout_time) : sessionLogoutTimeMs;

            if (logoutTimeMs) {
                sessionLogoutTimeMs = logoutTimeMs;
            }

            if (logoutTimeMs && !sessionState.logoutMarkerAdded) {
                const logoutMinute = toChartMinutes(sessionState.chartStartTime, logoutTimeMs);
                addMarker(logoutMinute, 'Session User Logout', '#7c3aed', 'session', 'callout');
                sessionState.logoutMarkerAdded = true;
            }

            appendPoint(sessionState, denialMinute, 0);

            sessionAccessInvalidMs = denialTimeMs;
            renderSessionRL();
            renderChart();
            saveChartState();
            scrollToChart();
        }

        function handleSessionVictimLogout() {
            if (!sessionState.chartStartTime || !sessionLogoutTimeMs) {
                return;
            }

            const logoutMinute = toChartMinutes(sessionState.chartStartTime, sessionLogoutTimeMs);

            if (!sessionState.logoutMarkerAdded) {
                addMarker(logoutMinute, 'Session User Logout', '#7c3aed', 'session', 'callout');
                sessionState.logoutMarkerAdded = true;
            }

            renderSessionRL();
            ensureChartVisible();
            renderChart();
            saveChartState();
        }

        function handleTokenVictimLogout() {
            if (!tokenState.chartStartTime || tokenState.logoutMarkerAdded || !tokenLogoutTimeMs) {
                return;
            }

            const logoutMinute = toChartMinutes(tokenState.chartStartTime, tokenLogoutTimeMs);

            addMarker(
                logoutMinute,
                'Token User Logout',
                '#7c3aed',
                'token',
                'callout'
            );
            tokenState.logoutMarkerAdded = true;
            renderTokenRL();
            ensureChartVisible();
            renderChart();
            saveChartState();
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
            const denialMinute = toChartMinutes(tokenState.chartStartTime, denialTimeMs);
            const expirationMinute = tokenExpiryChartMinute;

            if (!tokenState.expiryMarkerAdded) {
                addMarker(expirationMinute, 'Token Expired', '#dc2626', 'token', 'callout');
                tokenState.expiryMarkerAdded = true;
            }

            appendPoint(tokenState, expirationMinute, 1, { hidden: true });
            appendPoint(tokenState, expirationMinute, 0, { hidden: true });

            if (denialMinute > expirationMinute) {
                appendPoint(tokenState, denialMinute, 0);
            }

            tokenAccessInvalidMs = tokenState.chartStartTime + (tokenTtlSeconds * 1000);

            renderTokenRL();
            renderChart();
            saveChartState();
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

        resetAttackBoxesButton.addEventListener('click', async function (event) {
            event.preventDefault();
            try {
                await fetch(resetCapturedCredentialsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
            } catch (error) {
                console.error('Failed to clear captured phishing credentials.', error);
            }
            resetAttackBoxes();
        });

        resetChartButton.addEventListener('click', async function (event) {
            event.preventDefault();
            try {
                await fetch(resetCapturedCredentialsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
            } catch (error) {
                console.error('Failed to clear captured phishing credentials.', error);
            }
            clearResetCookie();
            resetChartData();
        });

        sessionButton.addEventListener('click', async function () {
            const capturedValue = sessionInput.value.trim();

            if (!capturedValue) {
                showAlert('Please enter the captured session ID before continuing.');
                return;
            }

            sendSecurityAlertToServer('session', { session_id: capturedValue });
            hideAlert();
            ensureChartVisible();
            saveChartState();

            try {
                const data = await postJson(validateSessionUrl, { session_id: capturedValue });
                applyServerLogoutTime(data, 'session');

                if (data.valid) {
                    if (!sessionState.chartStartTime) {
                        startTest(sessionState, 'session', resolveChartStartTime('session', data, sessionPhishTs));
                    }

                    sessionState.unauthorizedAttempts = (sessionState.unauthorizedAttempts || 0) + 1;
                    recordSuccessfulAccess(sessionState, data, 'session');

                    if (sessionLogoutTimeMs && !sessionState.logoutMarkerAdded) {
                        handleSessionVictimLogout();
                    }

                    return;
                }

                if (!sessionState.chartStartTime) {
                    showAlert('Captured session ID is not valid. Start with a live captured session before the victim logs out.');
                    return;
                }

                sessionState.unauthorizedAttempts = (sessionState.unauthorizedAttempts || 0) + 1;
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

            sendSecurityAlertToServer('token', { token: capturedValue });
            hideAlert();
            ensureChartVisible();
            saveChartState();

            try {
                const data = await postJson(validateTokenUrl, { token: capturedValue });
                applyServerLogoutTime(data, 'token');
                lastTokenValidation = data;

                // If the server refreshed the token, keep the form/display in sync so the
                // 5-minute countdown restarts on every click.
                if (data.token && data.token !== capturedValue) {
                    tokenInput.value = data.token;
                    if (tokenVictimToken) tokenVictimToken.textContent = data.token;
                }

                const hasVictimLogout = Boolean(tokenLogoutTimeMs || data.logout_time);
                const logoutValue = tokenLogoutTimeMs
                    ? new Date(tokenLogoutTimeMs).toISOString()
                    : (data.logout_time || null);
                const tokenData = {
                    ...data,
                    logout_time: logoutValue,
                    logout_occurred: hasVictimLogout,
                };

                if (isTokenAccessValid(tokenData)) {
                    if (!tokenState.chartStartTime) {
                        startTest(tokenState, 'token', resolveChartStartTime('token', data, tokenPhishTs));
                    }

                    tokenState.unauthorizedAttempts = (tokenState.unauthorizedAttempts || 0) + 1;

                    if (hasVictimLogout && !tokenState.logoutMarkerAdded) {
                        handleTokenVictimLogout();
                    }

                    recordSuccessfulAccess(tokenState, tokenData, 'token', {
                        onLogoutWhileValid: hasVictimLogout,
                        logoutLabel: 'Token User Logout',
                        markerStyle: 'callout',
                        logoutTimeMs: tokenLogoutTimeMs,
                    });

                    return;
                }

                if (!tokenState.chartStartTime) {
                    showAlert('Captured JWT token is not valid. Log in via token authentication and capture a fresh JWT before testing.');
                    return;
                }

                tokenState.unauthorizedAttempts = (tokenState.unauthorizedAttempts || 0) + 1;

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
