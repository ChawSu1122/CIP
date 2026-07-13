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
<body>
    <div id="app">
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
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('forum.features') }}">Features</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="authDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Auth Demo
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="authDropdown">
                                <li><a class="dropdown-item" href="{{ route('session.login') }}">Session Login</a></li>
                                <li><a class="dropdown-item" href="{{ route('token.login') }}">Token Login</a></li>
                                <li><a class="dropdown-item" href="{{ route('session.register') }}">Session Register</a></li>
                                <li><a class="dropdown-item" href="{{ route('token.register') }}">Token Register</a></li>
                                <li><a class="dropdown-item" href="{{ route('token.demo') }}">Token Demo</a></li>
                                <li><a class="dropdown-item" href="{{ route('api.docs') }}">API Docs</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="researchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Thesis
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="researchDropdown">
                                <li><a class="dropdown-item" href="{{ route('comparison.dashboard') }}">Comparison Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard.scalability') }}">Scalability</a></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard.storage') }}">Storage</a></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard.security') }}">Security</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('thesis.questions') }}">Research Questions</a></li>
                                <li><a class="dropdown-item" href="{{ route('thesis.methodology') }}">Methodology</a></li>
                                <li><a class="dropdown-item" href="{{ route('thesis.experiment') }}">Experiment Results</a></li>
                                <li><a class="dropdown-item" href="{{ route('thesis.security') }}">Security Analysis</a></li>
                                <li><a class="dropdown-item" href="{{ route('thesis.replay') }}">Replay Attack Demo</a></li>
                                <li><a class="dropdown-item" href="{{ route('thesis.complexity') }}">Complexity Analysis</a></li>
                                <li><a class="dropdown-item" href="{{ route('presentation.summary') }}">Presentation Summary</a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

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

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
