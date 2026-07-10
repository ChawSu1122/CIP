<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.ThesisCharts = (function () {
    const colors = { session: '#0d6efd', token: '#20c997' };
    const charts = {};

    function destroy(id) {
        if (charts[id]) {
            charts[id].destroy();
            delete charts[id];
        }
    }

    function barChart(id, labels, datasets, title) {
        destroy(id);
        const el = document.getElementById(id);
        if (!el) return;
        charts[id] = new Chart(el.getContext('2d'), {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { title: { display: !!title, text: title || '' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    function pieChart(id, labels, data, title) {
        destroy(id);
        const el = document.getElementById(id);
        if (!el) return;
        charts[id] = new Chart(el.getContext('2d'), {
            type: 'pie',
            data: {
                labels,
                datasets: [{ data, backgroundColor: [colors.session, colors.token] }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { title: { display: !!title, text: title || '' } },
            },
        });
    }

    function doughnutChart(id, labels, data, title) {
        destroy(id);
        const el = document.getElementById(id);
        if (!el) return;
        charts[id] = new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: [colors.session, colors.token] }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { title: { display: !!title, text: title || '' } },
            },
        });
    }

    function winnerLabel(winner) {
        return winner === 'session' ? 'Session' : 'Token';
    }

    function renderComparison(data) {
        const totals = data.totals || {};
        const hasData = (totals.session_logins || 0) + (totals.token_logins || 0) > 0;
        const emptyEl = document.getElementById('metrics-empty');
        if (emptyEl) emptyEl.classList.toggle('d-none', hasData);

        const s = data.scalability;
        const st = data.storage;
        const sec = data.security;

        barChart('scalability-score-chart', ['Session', 'Token'], [{
            label: 'Scalability score',
            data: [s.session.score, s.token.score],
            backgroundColor: [colors.session, colors.token],
        }], 'Scalability Score (higher = better)');

        barChart('scalability-duration-chart', ['Duration (ms)', 'Queries', 'Memory (KB)'], [
            {
                label: 'Session',
                data: [s.session.avg_duration_ms, s.session.avg_queries, s.session.avg_memory_bytes / 1024],
                backgroundColor: colors.session,
            },
            {
                label: 'Token',
                data: [s.token.avg_duration_ms, s.token.avg_queries, s.token.avg_memory_bytes / 1024],
                backgroundColor: colors.token,
            },
        ], 'Scalability Measurements (lower = better)');

        document.getElementById('scalability-verdict').innerHTML =
            '<strong>Winner: ' + winnerLabel(s.winner) + '</strong><br>' + s.verdict;

        barChart('storage-bytes-chart', ['Session', 'Token'], [{
            label: 'Avg bytes per login',
            data: [st.session.avg_bytes_per_login, st.token.avg_bytes_per_login],
            backgroundColor: [colors.session, colors.token],
        }], 'Storage per Login (bytes)');

        barChart('storage-total-chart', ['Session', 'Token'], [{
            label: 'Total server bytes',
            data: [st.session.total_bytes, st.token.total_bytes],
            backgroundColor: [colors.session, colors.token],
        }], 'Total Server Storage (bytes)');

        document.getElementById('storage-verdict').innerHTML =
            '<strong>Winner: ' + winnerLabel(st.winner) + '</strong><br>' + st.verdict;

        barChart('security-score-chart', ['Session', 'Token'], [{
            label: 'Security score',
            data: [sec.session.score, sec.token.score],
            backgroundColor: [colors.session, colors.token],
        }], 'Security Score (higher = better)');

        barChart('security-success-chart', ['Replay succeeded', 'Replay blocked'], [
            {
                label: 'Session',
                data: [sec.session.replay_success, sec.session.replay_blocked],
                backgroundColor: colors.session,
            },
            {
                label: 'Token',
                data: [sec.token.replay_success, sec.token.replay_blocked],
                backgroundColor: colors.token,
            },
        ], 'Replay Attack Results');

        const securityVerdictEl = document.getElementById('security-verdict');
        if (securityVerdictEl) {
            const replayLink = '<a href="{{ url('/thesis/replay-attack') }}" class="alert-link">Run replay demo</a>';
            securityVerdictEl.innerHTML =
                '<strong>Winner: ' + winnerLabel(sec.winner) + '</strong><br>' + sec.verdict +
                '<br><small class="text-muted">Replay attacks: Session ' + sec.session.replay_success + ' succeeded / ' +
                sec.session.replay_blocked + ' blocked · Token ' + sec.token.replay_success +
                ' succeeded / ' + sec.token.replay_blocked + ' blocked. ' + replayLink + '</small>';
        }

        barChart('overall-bar-chart', ['Scalability', 'Storage', 'Security'], [
            {
                label: 'Session',
                data: [s.session.score, st.session.score, sec.session.score],
                backgroundColor: colors.session,
            },
            {
                label: 'Token',
                data: [s.token.score, st.token.score, sec.token.score],
                backgroundColor: colors.token,
            },
        ], 'Comparison Scores by Criterion');

        doughnutChart('overall-pie-chart', ['Session avg', 'Token avg'], [
            Math.round((s.session.score + st.session.score + sec.session.score) / 3),
            Math.round((s.token.score + st.token.score + sec.token.score) / 3),
        ], 'Overall Average Score');

        const tbody = document.querySelector('#metrics-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr><td>Scalability score</td><td>${s.session.score}</td><td>${s.token.score}</td><td>${winnerLabel(s.winner)}</td></tr>
                <tr><td>Avg login time (ms)</td><td>${s.session.avg_duration_ms}</td><td>${s.token.avg_duration_ms}</td><td>${s.session.avg_duration_ms <= s.token.avg_duration_ms ? 'Session' : 'Token'}</td></tr>
                <tr><td>Storage per login (bytes)</td><td>${st.session.avg_bytes_per_login}</td><td>${st.token.avg_bytes_per_login}</td><td>${winnerLabel(st.winner)}</td></tr>
                <tr><td>Total server storage (bytes)</td><td>${st.session.total_bytes}</td><td>${st.token.total_bytes}</td><td>${st.session.total_bytes <= st.token.total_bytes ? 'Session' : 'Token'}</td></tr>
                <tr><td>Security score</td><td>${sec.session.score}</td><td>${sec.token.score}</td><td>${winnerLabel(sec.winner)}</td></tr>
                <tr><td>Replay attacks succeeded</td><td>${sec.session.replay_success}</td><td>${sec.token.replay_success}</td><td>${sec.session.replay_success <= sec.token.replay_success ? 'Session' : 'Token'}</td></tr>
                <tr><td>Replay attacks blocked</td><td>${sec.session.replay_blocked}</td><td>${sec.token.replay_blocked}</td><td>—</td></tr>
                <tr><td>Replay vulnerability %</td><td>${sec.session.replay_vulnerability_rate}</td><td>${sec.token.replay_vulnerability_rate}</td><td>${sec.session.replay_vulnerability_rate <= sec.token.replay_vulnerability_rate ? 'Session' : 'Token'}</td></tr>
                <tr><td>Failed logins</td><td>${sec.session.failed_logins}</td><td>${sec.token.failed_logins}</td><td>—</td></tr>
                <tr><td>Total logins recorded</td><td>${totals.session_logins || 0}</td><td>${totals.token_logins || 0}</td><td>—</td></tr>
            `;
        }
    }

    function renderScalabilityPage(data) {
        const s = data.scalability;
        barChart('scalability-bar', ['Score', 'Duration (ms)', 'Queries'], [
            {
                label: 'Session',
                data: [s.session.score, s.session.avg_duration_ms, s.session.avg_queries],
                backgroundColor: colors.session,
            },
            {
                label: 'Token',
                data: [s.token.score, s.token.avg_duration_ms, s.token.avg_queries],
                backgroundColor: colors.token,
            },
        ], 'Scalability Measurements');

        pieChart('scalability-pie', ['Session logins', 'Token logins'], [
            s.session.login_count,
            s.token.login_count,
        ], 'Login Distribution');

        const verdict = document.getElementById('scalability-verdict');
        if (verdict) {
            verdict.innerHTML = '<strong>Winner: ' + winnerLabel(s.winner) + '</strong> — ' + s.verdict;
        }

        const stats = document.getElementById('scalability-stats');
        if (stats) {
            stats.innerHTML = `
                <li>Session avg response: <strong>${s.session.avg_duration_ms} ms</strong></li>
                <li>Token avg response: <strong>${s.token.avg_duration_ms} ms</strong></li>
                <li>Session avg queries: <strong>${s.session.avg_queries}</strong></li>
                <li>Token avg queries: <strong>${s.token.avg_queries}</strong></li>
            `;
        }
    }

    function renderStoragePage(data) {
        const st = data.storage;
        barChart('storage-bar', ['Bytes/login', 'Total bytes', 'Active records'], [
            {
                label: 'Session',
                data: [st.session.avg_bytes_per_login, st.session.total_bytes, st.session.active_records],
                backgroundColor: colors.session,
            },
            {
                label: 'Token',
                data: [st.token.avg_bytes_per_login, st.token.total_bytes, st.token.active_records],
                backgroundColor: colors.token,
            },
        ], 'Storage Comparison');

        pieChart('storage-pie', ['Session storage', 'Token storage'], [
            st.session.total_bytes || 1,
            st.token.total_bytes || 1,
        ], 'Total Server Storage Share');

        const verdict = document.getElementById('storage-verdict');
        if (verdict) {
            verdict.innerHTML = '<strong>Winner: ' + winnerLabel(st.winner) + '</strong> — ' + st.verdict;
        }

        const stats = document.getElementById('storage-stats');
        if (stats) {
            stats.innerHTML = `
                <li>Session payload avg: <strong>${st.session.avg_bytes_per_login} bytes</strong></li>
                <li>Token storage avg: <strong>${st.token.avg_bytes_per_login} bytes</strong></li>
                <li>Active sessions: <strong>${st.session.active_records}</strong></li>
                <li>Active tokens: <strong>${st.token.active_records}</strong></li>
            `;
        }
    }

    function renderSecurityPage(data) {
        const sec = data.security;

        barChart('security-bar', ['Security score', 'Replay succeeded', 'Replay blocked'], [
            {
                label: 'Session',
                data: [sec.session.score, sec.session.replay_success, sec.session.replay_blocked],
                backgroundColor: colors.session,
            },
            {
                label: 'Token',
                data: [sec.token.score, sec.token.replay_success, sec.token.replay_blocked],
                backgroundColor: colors.token,
            },
        ], 'Security & Replay Attack Measurements');

        barChart('security-replay-bar', ['Session', 'Token'], [
            {
                label: 'Replay succeeded (vulnerable)',
                data: [sec.session.replay_success, sec.token.replay_success],
                backgroundColor: '#dc3545',
            },
            {
                label: 'Replay blocked (mitigated)',
                data: [sec.session.replay_blocked, sec.token.replay_blocked],
                backgroundColor: '#198754',
            },
        ], 'Replay Attack: Succeeded vs Blocked');

        doughnutChart('security-pie', [
            'Session replay OK',
            'Session replay blocked',
            'Token replay OK',
            'Token replay blocked',
        ], [
            sec.session.replay_success || 0,
            sec.session.replay_blocked || 0,
            sec.token.replay_success || 0,
            sec.token.replay_blocked || 0,
        ], 'All Replay Attack Outcomes');

        const verdict = document.getElementById('security-verdict');
        if (verdict) {
            verdict.innerHTML =
                '<strong>Winner: ' + winnerLabel(sec.winner) + '</strong> — ' + sec.verdict +
                '<br><small>Lower replay success = better security. Run the <a href="{{ url('/thesis/replay-attack') }}">Replay Attack Demo</a> to generate data.</small>';
        }

        const stats = document.getElementById('security-stats');
        if (stats) {
            stats.innerHTML = `
                <li>Session CSRF protected: <strong>${sec.session.csrf_protected ? 'Yes' : 'No'}</strong></li>
                <li>Session HttpOnly cookie: <strong>${sec.session.http_only_cookie ? 'Yes' : 'No'}</strong></li>
                <li>Token bearer exposure risk: <strong>${sec.token.bearer_token_exposure ? 'Yes' : 'No'}</strong></li>
                <li>Session replay attempts: <strong>${sec.session.replay_attempts}</strong> (${sec.session.replay_success} succeeded, ${sec.session.replay_blocked} blocked)</li>
                <li>Token replay attempts: <strong>${sec.token.replay_attempts}</strong> (${sec.token.replay_success} succeeded, ${sec.token.replay_blocked} blocked)</li>
                <li>Session replay vulnerability: <strong>${sec.session.replay_vulnerability_rate}%</strong></li>
                <li>Token replay vulnerability: <strong>${sec.token.replay_vulnerability_rate}%</strong></li>
            `;
        }
    }

    async function fetchData(url) {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to load metrics');
        return response.json();
    }

    function bindRefresh(url, renderFn) {
        async function load() {
            try {
                const data = await fetchData(url);
                renderFn(data);
            } catch (e) {
                console.error(e);
            }
        }

        load();
        const btn = document.getElementById('refresh-metrics');
        if (btn) btn.addEventListener('click', load);
        setInterval(load, 5000);
    }

    return {
        initComparisonPage(url) {
            bindRefresh(url, renderComparison);
        },
        initScalabilityPage(url) {
            bindRefresh(url, renderScalabilityPage);
        },
        initStoragePage(url) {
            bindRefresh(url, renderStoragePage);
        },
        initSecurityPage(url) {
            bindRefresh(url, renderSecurityPage);
        },
    };
})();
</script>
