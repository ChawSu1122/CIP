@extends('dashboard.layout')

@section('title', 'Data Exposure Risk')

@section('content')
@php
    use App\Models\ExperimentMetric;

    $sessionPhish = ExperimentMetric::where('action', 'link_clicked')
        ->where('victim_authentication_type', 'session')
        ->orderBy('created_at', 'desc')
        ->first();

    $tokenPhish = ExperimentMetric::where('action', 'link_clicked')
        ->where('victim_authentication_type', 'token')
        ->orderBy('created_at', 'desc')
        ->first();

    $sessionVictimName = $sessionPhish?->victim_name ?? '—';
    $sessionVictimSession = $sessionPhish?->victim_session_id ?? '—';
    $sessionPhishTime = $sessionPhish?->created_at?->format('Y-m-d H:i:s') ?? '—';

    $tokenVictimName = $tokenPhish?->victim_name ?? '—';
    $tokenVictimToken = $tokenPhish?->victim_token ?? '—';
    $tokenPhishTime = $tokenPhish?->created_at?->format('Y-m-d H:i:s') ?? '—';
@endphp

    <div class="mb-3">
        <h1 class="page-title">Data Exposure Risk</h1>
        {{-- <p class="page-copy">This page compares how credential and identity data is exposed in session-based versus token-based authentication flows, with a simple payload simulation for each method.</p> --}}
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
                            {{-- <span class="fw-semibold">{{ $sessionVictimName }} is logged in and clicks the phishing link.</span> --}}
                            <span class="fw-semibold">@if($sessionPhish) {{ $sessionVictimName }} is logged in and clicks the phishing link. @else — @endif</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Captured session ID</span>
                            <span class="fw-semibold text-break">{{ $sessionVictimSession }}</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Authentication Method</span>
                            {{-- <span class="fw-semibold">Session-Based</span> --}}
                            <span class="fw-semibold">@if($sessionPhish) Session-Based @else — @endif</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Time</span>
                            <span class="fw-semibold">{{ $sessionPhishTime }}</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <textarea id="captured-session" class="form-control form-control-sm" rows="2" placeholder="Type captured session here..."></textarea>
                        </div>
                    </li>
                </ul>

                <div class="card-body">
                    <button id="analyze-session-risk" type="button" class="btn btn-primary">Analyze Session Risk</button>
                </div>

                {{-- <div class="card-body">
                    <label for="session-payload" class="form-label">Simulated Session Credential Payload</label>
                    <textarea id="session-payload" class="form-control" rows="6">{
                        "user_id": 101,
                        "email": "alice@example.com",
                        "role": "user",
                        "session_id": "AwFyOkIrjamLOK0OohcZZOsYKnIWECJZirbWOZ5b"
                        }</textarea>
                </div> --}}
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
                            {{-- <span class="fw-semibold">{{ $tokenVictimName }} is logged in and clicks the phishing link.</span> --}}
                            <span class="fw-semibold">@if($tokenPhish) {{ $tokenVictimName }} is logged in and clicks the phishing link. @else — @endif</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Captured token ID</span>
                            <span class="fw-semibold text-break">{{ $tokenVictimToken }}</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Authentication Method</span>
                            {{-- <span class="fw-semibold">Token-Based</span> --}}
                            <span class="fw-semibold">@if($tokenPhish) Token-Based @else — @endif</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Time</span>
                            <span class="fw-semibold">{{ $tokenPhishTime }}</span>
                        </div>
                    </li>

                    <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <textarea id="captured-token" class="form-control form-control-sm" rows="2" placeholder="Type captured token here..."></textarea>
                    </div>
                </li>
                </ul>

                <div class="card-body">
                    <button id="analyze-token-risk" type="button" class="btn btn-danger">Analyze Token Risk</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white border-0">
            <h2 class="h5 mb-1">Data Exposure Risk Comparison</h2>
            <p class="mb-0 small text-secondary">Compares exposed identity fields embedded in the captured session ID versus claims decoded from the captured JWT token.</p>
        </div>
        <div class="card-body">
            <div id="dataex-comparison-cards" class="row g-4"></div>
        </div>
    </div>

    <div id="exposure-alert" class="alert alert-warning d-none mt-4" role="alert"></div>

    <div class="row row-cols-1 row-cols-lg-2 gx-4 gy-4 mt-4 d-none">
        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 mb-2">Session Exposure Summary</h2>
                    <p class="text-secondary mb-3">This analysis inspects the captured session ID itself and counts any identity fields embedded in the credential string.</p>
                    <dl class="row mb-0">
                        <dt class="col-6">Session found</dt>
                        <dd class="col-6" id="session-found">—</dd>
                        <dt class="col-6">Session ID length</dt>
                        <dd class="col-6" id="session-id-length">—</dd>
                        <dt class="col-6">Exposed claims</dt>
                        <dd class="col-6" id="session-fields">—</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 mb-2">Token Exposure Summary</h2>
                    <p class="text-secondary mb-3">This analysis decodes the captured JWT payload and counts the identity claims exposed in the token.</p>
                    <dl class="row mb-0">
                        <dt class="col-6">Token found</dt>
                        <dd class="col-6" id="token-found">—</dd>
                        <dt class="col-6">Token length</dt>
                        <dd class="col-6" id="token-length">—</dd>
                        <dt class="col-6">Exposed claims</dt>
                        <dd class="col-6" id="token-elements">—</dd>
                    </dl>
                    <div class="mt-3">
                        <p class="mb-2 fw-semibold">Claim details</p>
                        <ul class="mb-0 ps-3" id="token-claim-list">
                            <li class="text-secondary">Run the token test to decode exposed JWT claims.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="comparison-result-card" class="card shadow-sm border-0 mt-4 d-none">
        <div class="card-body">
            <h3 class="h5 mb-2">Comparison Result</h3>
            <p id="comparison-result-text" class="mb-0 fw-bold text-success fs-4"></p>
        </div>
    </div>

    <div id="dataex-overall-chart-card" class="card shadow-sm border-0 mt-4 d-none">
        <div class="card-header bg-white border-0">
            <h3 class="h6 mb-1">Overall Comparison Result</h3>
            <p class="mb-0 small text-secondary">Average Session-Based and Token-Based Data Exposure Risk across all comparison results stored so far.</p>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 320px;">
                <canvas id="dataexOverallChart"></canvas>
            </div>
            <p class="mb-0 mt-3 small" id="dataex-overall-summary"></p>
        </div>
    </div>

    {{-- <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h2 class="h5 mb-2">Comparison Summary</h2>
            <p class="mb-0 text-secondary">Session-based authentication keeps most identity state on the server, while token-based authentication relies on a client-held credential that can be exposed more easily if copied, logged, or intercepted.</p>
        </div>
    </div> --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sessionInput = document.getElementById('captured-session');
        const tokenInput = document.getElementById('captured-token');
        const analyzeTokenButton = document.getElementById('analyze-token-risk');
        const analyzeSessionButton = document.getElementById('analyze-session-risk');
        const exposureAlert = document.getElementById('exposure-alert');
        const analyzeUrl = "{{ route('dashboard.data-exposure-risk.analyze') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const comparisonResultCard = document.getElementById('comparison-result-card');
        const comparisonResultText = document.getElementById('comparison-result-text');
        const comparisonsUrl = "{{ route('dashboard.data-exposure-risk.comparisons') }}";
        const dataexComparisonCardsContainer = document.getElementById('dataex-comparison-cards');
        const dataexOverallChartCard = document.getElementById('dataex-overall-chart-card');
        const dataexOverallCanvas = document.getElementById('dataexOverallChart');
        const dataexOverallSummary = document.getElementById('dataex-overall-summary');
        const dataexComparisonCharts = new Map();
        let dataexOverallChart = null;
        const chartState = {
            sessionCount: null,
            tokenCount: null,
            sessionFields: [],
            tokenFields: [],
        };

        function updateComparisonResult() {
            const sessionCount = chartState.sessionCount;
            const tokenCount = chartState.tokenCount;

            if (sessionCount === null || tokenCount === null) {
                comparisonResultCard.classList.add('d-none');
                return;
            }

            comparisonResultCard.classList.remove('d-none');

            if (sessionCount < tokenCount) {
                comparisonResultText.textContent = `Session is winner. Session-based authentication achieves a lower data exposure risk (${sessionCount} exposed fields) than token-based authentication (${tokenCount} exposed fields).`;
                comparisonResultText.className = 'mb-0 fw-bold text-success fs-4';
                return;
            }

            if (tokenCount < sessionCount) {
                comparisonResultText.textContent = 'Token is winner. Token-based authentication achieves a lower data exposure risk than session-based authentication.';
                comparisonResultText.className = 'mb-0 fw-bold text-danger fs-4';
                return;
            }

            comparisonResultText.textContent = `Both are tied. Both authentication methods expose the same number of fields (${sessionCount} exposed fields).`;
            comparisonResultText.className = 'mb-0 fw-bold text-warning fs-4';
        }

        const valueLabelPlugin = {
            id: 'valueLabel',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                ctx.save();
                ctx.font = 'bold 14px Inter, system-ui, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    chart.getDatasetMeta(datasetIndex).data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        if (value === null || value === undefined) {
                            return;
                        }

                        ctx.fillStyle = index === 0 ? '#2563eb' : '#dc3545';
                        ctx.fillText(String(value), bar.x, bar.y - 6);
                    });
                });

                ctx.restore();
            },
        };

        function showAlert(message) {
            exposureAlert.textContent = message;
            exposureAlert.classList.remove('d-none');
        }

        function hideAlert() {
            exposureAlert.classList.add('d-none');
            exposureAlert.textContent = '';
        }

        function formatFieldList(fields) {
            if (!fields || fields.length === 0) {
                return 'None detected';
            }

            return fields.join(', ');
        }

        function dataexDomId(value) {
            return String(value).replace(/[^a-zA-Z0-9_-]/g, '_');
        }

        function renderDataExposureComparisonCard(comparison, index) {
            const safeId = dataexDomId(comparison.comparison_id);
            const cardId = `dataex-cmp-card-${safeId}`;
            const chartId = `dataex-cmp-chart-${safeId}`;
            const summaryId = `dataex-cmp-summary-${safeId}`;
            let wrapper = document.getElementById(cardId);

            if (!wrapper) {
                wrapper = document.createElement('div');
                wrapper.id = cardId;
                wrapper.className = 'col-12 col-lg-6';
                wrapper.innerHTML = `
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0">
                            <h3 class="h6 mb-1">Comparison Result ${index + 1}</h3>
                            <p class="mb-0 small text-secondary">Session-based: ${comparison.session_victim_name || '—'} &nbsp;•&nbsp; Token-based: ${comparison.token_victim_name || '—'}</p>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 260px;">
                                <canvas id="${chartId}"></canvas>
                            </div>
                            <p class="mb-0 mt-3 small" id="${summaryId}"></p>
                        </div>
                    </div>
                `;
                dataexComparisonCardsContainer.appendChild(wrapper);
            }

            const canvas = document.getElementById(chartId);
            const summary = document.getElementById(summaryId);
            const values = [
                comparison.session_count ?? null,
                comparison.token_count ?? null,
            ];

            const existingChart = dataexComparisonCharts.get(comparison.comparison_id);
            if (existingChart) {
                existingChart.data.datasets[0].data = values;
                existingChart.update();
            } else if (canvas) {
                dataexComparisonCharts.set(comparison.comparison_id, new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    plugins: [valueLabelPlugin],
                    data: {
                        labels: ['Session-Based', 'Token-Based'],
                        datasets: [{
                            label: 'Exposed fields',
                            data: values,
                            backgroundColor: ['#2563eb', '#dc3545'],
                            borderRadius: 4,
                            maxBarThickness: 120,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Authentication Method',
                                },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Exposed Fields (Counts)',
                                },
                            },
                        },
                    },
                }));
            }

            if (summary) {
                const sessionCount = comparison.session_count === null ? 'Pending' : `${comparison.session_count} field${comparison.session_count === 1 ? '' : 's'}`;
                const tokenCount = comparison.token_count === null ? 'Pending' : `${comparison.token_count} field${comparison.token_count === 1 ? '' : 's'}`;
                summary.innerHTML = `
                    <strong class="text-primary">Session-Based:</strong> ${sessionCount}<br>
                    <strong class="text-danger">Token-Based:</strong> ${tokenCount}
                `;
            }
        }

        function formatDataexAverage(value) {
            if (value === null || value === undefined) {
                return 'Pending';
            }

            const rounded = Math.round(Number(value) * 100) / 100;
            return Number.isInteger(rounded) ? String(rounded) : rounded.toFixed(1);
        }

        function renderDataExposureOverallChart(overall) {
            if (!overall || !dataexOverallChartCard || !dataexOverallCanvas) {
                if (dataexOverallChartCard) {
                    dataexOverallChartCard.classList.add('d-none');
                }
                return;
            }

            const values = [
                overall.session_average ?? null,
                overall.token_average ?? null,
            ];

            const hasAnyValue = values.some(function (value) {
                return value !== null && value !== undefined;
            });

            if (!hasAnyValue) {
                dataexOverallChartCard.classList.add('d-none');
                return;
            }

            dataexOverallChartCard.classList.remove('d-none');

            if (!dataexOverallChart) {
                dataexOverallChart = new Chart(dataexOverallCanvas.getContext('2d'), {
                    type: 'bar',
                    plugins: [valueLabelPlugin],
                    data: {
                        labels: ['Overall Session-Based', 'Overall Token-Based'],
                        datasets: [{
                            label: 'Average Exposed Fields',
                            data: values,
                            backgroundColor: ['#2563eb', '#dc3545'],
                            borderRadius: 4,
                            maxBarThickness: 120,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `Average exposed fields: ${formatDataexAverage(context.parsed.y)}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Authentication Method',
                                },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                                title: {
                                    display: true,
                                    text: 'Average Number of Exposed Fields (Counts)',
                                },
                            },
                        },
                    },
                });
            } else {
                dataexOverallChart.data.datasets[0].data = values;
                dataexOverallChart.update();
            }

            if (dataexOverallSummary) {
                const sessionLabel = overall.session_average !== null && overall.session_average !== undefined
                    ? `${formatDataexAverage(overall.session_average)} fields`
                    : 'Pending';
                const tokenLabel = overall.token_average !== null && overall.token_average !== undefined
                    ? `${formatDataexAverage(overall.token_average)} fields`
                    : 'Pending';

                dataexOverallSummary.innerHTML = `
                    <strong class="text-primary">Overall Session-Based:</strong> ${sessionLabel}
                    (${overall.session_count ?? 0} result${(overall.session_count ?? 0) === 1 ? '' : 's'})<br>
                    <strong class="text-danger">Overall Token-Based:</strong> ${tokenLabel}
                    (${overall.token_count ?? 0} result${(overall.token_count ?? 0) === 1 ? '' : 's'})
                `;
            }
        }

        async function refreshDataExposureResults() {
            try {
                const response = await fetch(comparisonsUrl, {
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();

                if (data.success) {
                    (data.comparisons || []).forEach(renderDataExposureComparisonCard);
                    renderDataExposureOverallChart(data.overall);
                }
            } catch (error) {
                console.error('Unable to refresh data exposure risk comparison results.', error);
            }
        }

        function updateSessionSummary(sessionData) {
            const sessionBytesEl = document.getElementById('session-bytes');
            const sessionFieldListEl = document.getElementById('session-field-list');

            document.getElementById('session-found').textContent = sessionData?.found ? 'Yes' : 'Yes';

            if (sessionBytesEl) {
                sessionBytesEl.textContent = sessionData?.payload_bytes ?? '—';
            }

            document.getElementById('session-fields').textContent = sessionData?.exposed_field_count ?? '—';

            if (sessionFieldListEl) {
                sessionFieldListEl.textContent = formatFieldList(sessionData?.exposed_fields);
            }

            document.getElementById('session-id-length').textContent = sessionData?.session_id_length ?? '—';

            if (sessionData?.analyzed) {
                const detailEl = document.getElementById('session-chart-detail');
                if (detailEl) {
                    detailEl.textContent =
                        `IES = ${sessionData.exposed_field_count} (${formatFieldList(sessionData.exposed_fields)})`;
                }
            }
        }

        function formatClaimDetails(claimDetails) {
            if (!claimDetails || claimDetails.length === 0) {
                return '<li class="text-secondary">No identity claims detected in the captured token.</li>';
            }

            return claimDetails.map((claim) => {
                const label = claim.claim.charAt(0).toUpperCase() + claim.claim.slice(1);
                return `<li><strong>${label}</strong>: ${claim.value} (${claim.description})</li>`;
            }).join('');
        }

        function formatClaimSummary(claimDetails) {
            if (!claimDetails || claimDetails.length === 0) {
                return 'None detected';
            }

            return claimDetails.map((claim) => claim.claim).join(', ');
        }

        function updateTokenSummary(tokenData) {
            document.getElementById('token-found').textContent = tokenData?.found ? 'Yes' : 'No';
            document.getElementById('token-length').textContent = tokenData?.token_length ?? '—';
            document.getElementById('token-elements').textContent = tokenData?.exposed_field_count ?? '—';
            document.getElementById('token-claim-list').innerHTML = formatClaimDetails(tokenData?.claim_details);

            if (tokenData?.analyzed) {
                const detailEl = document.getElementById('token-chart-detail');
                if (detailEl) {
                    detailEl.textContent =
                        `IES = ${tokenData.exposed_field_count} (${formatClaimSummary(tokenData.claim_details)})`;
                }
            }
        }

        async function analyzeCredential(payload) {
            hideAlert();

            try {
                const response = await fetch(analyzeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const error = await response.json();
                    showAlert(error.message || 'Unable to analyze credentials.');
                    return null;
                }

                return await response.json();
            } catch (error) {
                showAlert('Unable to reach the analysis service.');
                return null;
            }
        }

        analyzeSessionButton.addEventListener('click', async () => {
            const sessionId = sessionInput.value.trim();

            if (!sessionId) {
                showAlert('Enter a captured session ID to analyze.');
                return;
            }

            const data = await analyzeCredential({ session_id: sessionId });
            if (!data?.session) {
                return;
            }

            chartState.sessionCount = data.session.exposed_field_count;
            chartState.sessionFields = data.session.exposed_fields || [];
            updateSessionSummary(data.session);
            updateComparisonResult();
            refreshDataExposureResults();
        });

        analyzeTokenButton.addEventListener('click', async () => {
            const token = tokenInput.value.trim();

            if (!token) {
                showAlert('Enter a captured token to analyze.');
                return;
            }

            const data = await analyzeCredential({ token });
            if (!data?.token) {
                return;
            }

            chartState.tokenCount = data.token.exposed_field_count;
            chartState.tokenFields = data.token.exposed_fields || [];
            updateTokenSummary(data.token);
            updateComparisonResult();
            refreshDataExposureResults();
        });

        refreshDataExposureResults();
        updateComparisonResult();
    });
</script>
@endsection
