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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container-fluid container-lg">
                <span>
                    @auth
                    <span class="d-md-none me-1 py-1 px-2"
                            data-bs-toggle="offcanvas" data-bs-target="#sideNavOffcanvas" aria-controls="sideNavOffcanvas">
                        <i class="bi bi-list"></i>
                    </span>
                    @endauth
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </span>

                <div class="navbar">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login') && $hasUsers)
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
                            <li class="nav-item">
                                <a class="nav-link" href="#">{{ Auth::user()->name }}</a>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @auth
                <div class="container-fluid container-lg">
                    <div class="row justify-content-center">
                        <div class="col-md-9">
                            @yield('content')
                        </div>
                        <div class="col-md-3 d-none d-md-block order-md-first">
                            <div class="card mb-4">
                                <div class="card-header">Navigation</div>
                                <div class="card-body">
                                    @include('layouts.partials.navigation')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @yield('content')
            @endauth
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    @auth
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sideNavOffcanvas" aria-labelledby="sideNavOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sideNavOffcanvasLabel">Navigation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('layouts.partials.navigation')
        </div>
    </div>
    @endauth

    <!-- Pull Updates overlay -->
    <div id="pull-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center flex-column gap-3"
         style="background:rgba(0,0,0,0.6);z-index:9999;">
        <div class="spinner-border text-light" style="width:3rem;height:3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-white fw-semibold mb-0">Pulling updates&hellip;</p>
    </div>

    <script>
        (function () {
            var form = document.getElementById('git-pull-form');
            if (!form) return;
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Pull latest changes from GitHub and run migrations?')) return;
                var overlay = document.getElementById('pull-overlay');
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
                form.submit();
            });
        })();
    </script>
</body>
</html>
