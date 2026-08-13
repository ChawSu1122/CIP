<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body @auth data-victim-auth-type="{{ session('victim_authentication_type', 'session') }}" @endauth>
    <div id="app">
        @unless(View::hasSection('hideNavbar'))
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">Feed</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('categories.index') }}">Categories</a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="{{ route('forum.features') }}">Features</a>
                            </li> -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="authDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Authentication Login
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="authDropdown">
                                    <li><a class="dropdown-item" href="{{ route('session.login') }}">Session Login</a></li>
                                    <li><a class="dropdown-item" href="{{ route('token.login') }}">Token Login</a></li>
                                    <!-- <li><a class="dropdown-item" href="{{ route('token.demo') }}">Token Demo</a></li>
                                    <li><a class="dropdown-item" href="{{ route('api.docs') }}">API Docs</a></li> -->
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <!-- <a class="nav-link dropdown-toggle" href="#" id="researchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Options
                                </a> -->
                                <ul class="dropdown-menu" aria-labelledby="researchDropdown">
                                    <!-- <li><a class="dropdown-item" href="{{ route('comparison.dashboard') }}">Comparison Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li> -->
                                    <!-- <li><a class="dropdown-item" href="{{ route('dashboard.scalability') }}">Scalability</a></li>
                                    <li><a class="dropdown-item" href="{{ route('dashboard.storage') }}">Storage</a></li> -->
                                    <!-- <li><a class="dropdown-item" href="{{ route('dashboard.security') }}">Security Comparison Dashboard</a></li> -->
                                    <!-- <li><hr class="dropdown-divider"></li> -->
                                    <!-- <li><a class="dropdown-item" href="{{ route('thesis.questions') }}">Research Questions</a></li>
                                    <li><a class="dropdown-item" href="{{ route('thesis.methodology') }}">Methodology</a></li>
                                    <li><a class="dropdown-item" href="{{ route('thesis.experiment') }}">Experiment Results</a></li>
                                    <li><a class="dropdown-item" href="{{ route('thesis.security') }}">Security Analysis</a></li> -->
                                    <li><a class="dropdown-item" href="{{ route('thesis.replay') }}">Session Hijacking Attack</a></li>
                                    <!-- <li><a class="dropdown-item" href="{{ route('thesis.complexity') }}">Complexity Analysis</a></li>
                                    <li><a class="dropdown-item" href="{{ route('presentation.summary') }}">Presentation Summary</a></li> -->
                                </ul>
                            </li>
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <!-- Authentication Links -->
                            @guest
                                {{-- @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif --}}

                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
        @endunless

        <main class="py-4">
            @yield('content')
        </main>
    </div>

@auth
<div id="victim-security-alert-backdrop" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(15, 23, 42, 0.55); z-index: 2000;"></div>
<div id="victim-security-alert" class="position-fixed top-50 start-50 translate-middle d-none" style="z-index: 2001; width: min(92vw, 520px);">
    <div class="alert alert-warning border border-warning shadow-lg mb-0" role="alert">
        <div class="d-flex flex-column gap-2">
            <strong>Security Alert</strong>
            <p id="victim-security-alert-message" class="mb-0">Someone is trying to use your account. So if it is not you, please logout of all devices.</p>
            <div class="d-flex gap-2 mt-2">
                <button id="victim-security-yes" type="button" class="btn btn-sm btn-success">Yes, it is me</button>
                <button id="victim-security-no" type="button" class="btn btn-sm btn-danger">No, log out of all devices</button>
            </div>
        </div>
    </div>
</div>
@endauth

<script>
    function recordVictimLogout(authType) {
        const type = authType === 'token' ? 'token' : 'session';
        const at = String(Date.now());

        window.dispatchEvent(new CustomEvent('victim-logout', { detail: { type } }));
        window.localStorage.setItem(`victim-logout-event-${type}`, at);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const logoutForm = document.getElementById('logout-form');

        if (!logoutForm) {
            return;
        }

        logoutForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const authType = document.body.dataset.victimAuthType || 'session';
            recordVictimLogout(authType);

            fetch('/victim/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).finally(function () {
                logoutForm.submit();
            });
        });
    });

    @auth
    (function () {
        const securityAlertStatusUrl = @json(route('dashboard.revocation-latency.security-alert.status'));
        const securityAlertRespondUrl = @json(route('dashboard.revocation-latency.security-alert.respond'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const victimSecurityAlert = document.getElementById('victim-security-alert');
        const victimSecurityBackdrop = document.getElementById('victim-security-alert-backdrop');
        const victimSecurityMessage = document.getElementById('victim-security-alert-message');
        const victimSecurityYes = document.getElementById('victim-security-yes');
        const victimSecurityNo = document.getElementById('victim-security-no');
        let activeEventId = null;
        let responding = false;

        function showVictimSecurityAlert(message) {
            if (!victimSecurityAlert || !victimSecurityBackdrop) {
                return;
            }

            if (message && victimSecurityMessage) {
                victimSecurityMessage.textContent = message;
            }

            victimSecurityBackdrop.classList.remove('d-none');
            victimSecurityAlert.classList.remove('d-none');
        }

        function hideVictimSecurityAlert() {
            if (!victimSecurityAlert || !victimSecurityBackdrop) {
                return;
            }

            victimSecurityBackdrop.classList.add('d-none');
            victimSecurityAlert.classList.add('d-none');
            activeEventId = null;
        }

        function clearStaleSecurityAlert() {
            if (victimSecurityAlert) {
                victimSecurityAlert.classList.add('d-none');
            }
            if (victimSecurityBackdrop) {
                victimSecurityBackdrop.classList.add('d-none');
            }
            activeEventId = null;
        }

        async function pollSecurityAlert() {
            if (responding || activeEventId) {
                return;
            }

            try {
                const response = await fetch(securityAlertStatusUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    clearStaleSecurityAlert();
                    return;
                }

                const data = await response.json();
                if (data.active && data.event_id) {
                    activeEventId = data.event_id;
                    showVictimSecurityAlert(data.message);
                } else {
                    clearStaleSecurityAlert();
                }
            } catch (error) {
                console.error('Unable to poll victim security alert.', error);
            }
        }

        async function respondToSecurityAlert(action) {
            if (!activeEventId || responding) {
                return;
            }

            responding = true;

            try {
                const response = await fetch(securityAlertRespondUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        event_id: activeEventId,
                        action: action,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    hideVictimSecurityAlert();
                    return;
                }

                hideVictimSecurityAlert();

                if (data.action === 'logout') {
                    recordVictimLogout(data.type === 'token' ? 'token' : 'session');
                    window.location.href = data.redirect || @json(route('login'));
                }
            } catch (error) {
                console.error('Unable to respond to security alert.', error);
            } finally {
                responding = false;
            }
        }

        if (victimSecurityYes) {
            victimSecurityYes.addEventListener('click', function () {
                respondToSecurityAlert('acknowledge');
            });
        }

        if (victimSecurityNo) {
            victimSecurityNo.addEventListener('click', function () {
                respondToSecurityAlert('logout');
            });
        }

        pollSecurityAlert();
        setInterval(pollSecurityAlert, 1500);
    })();
    @endauth
</script>
</body>
</html>
