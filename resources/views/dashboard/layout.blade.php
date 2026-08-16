<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Security Testing Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"  />
    <style>
        :root {
            color-scheme: light;
            background: #f8fafc;
            color: #111827;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f8fafc;
        }

        .page {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            gap: 24px;
            /* padding: 24px; */
            padding : 0;
        }

        .sidebar {
            background: #0f172a;
            /* border-radius: 28px; */
            padding: 32px 24px;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 28px;
            box-shadow: 0 40px 80px rgba(15, 23, 42, 0.15);
        }

        .sidebar__brand {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #93c5fd;
        }

        .sidebar__title {
            margin: 0;
            font-size: 2.25rem;
            font-weight: 900;
            line-height: 1.05;
            color: #f8fafc;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }

        .menu-item {
            display: block;
            padding: 14px 18px;
            border-radius: 18px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            color: #dbeafe;
            background: rgba(148, 163, 184, 0.08);
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .menu-item:hover {
            transform: translateX(2px);
            background: rgba(148, 163, 184, 0.16);
        }

        .menu-item.active {
            background: #2563eb;
            color: #ffffff;
        }

        .content {
            /* display: grid; */
            /* gap: 24px; */
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .page-title {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f172a;
        }

        .page-copy {
            margin: 12px 0 0;
            color: #475569;
            max-width: 720px;
            font-size: 1rem;
            line-height: 1.8;
        }

        .panel {
            min-height: 460px;
            border-radius: 32px;
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 34px 80px rgba(15, 23, 42, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .panel-text {
            color: #64748b;
            font-size: 1rem;
            text-align: center;
        }

        @media (max-width: 1030px) {
            .page {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            .page {
                padding: 16px;
            }

            .sidebar {
                padding: 24px 18px;
            }

            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <aside class="sidebar">
            <div>
                <div class="sidebar__brand">Security Testing</div>
                <h1 class="sidebar__title">Dashboard</h1>
            </div>

            <ul class="menu">
                <!-- <li>
                    <a href="/dashboard" class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
                </li> -->
                <li>
                    <a href="/dashboard/revocation-latency" class="menu-item {{ request()->is('dashboard/revocation-latency') ? 'active' : '' }}">Revocation Latency & Attack Success Rate</a>
                </li>
                <li>
                    <a href="/dashboard/data-exposure-risk" class="menu-item {{ request()->is('dashboard/data-exposure-risk') ? 'active' : '' }}">Data Exposure Risk</a>
                </li>
            </ul>
        </aside>

        <main class="container-fluid content">
            @yield('content')
        </main>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" ></script>

</body>
</html>
