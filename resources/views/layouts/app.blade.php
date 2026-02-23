<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Contact Management')</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .table thead {
            background-color: #343a40;
            color: #fff;
        }

        .action-btn {
            min-width: 80px;
        }

        footer {
            background-color: #343a40;
            color: #adb5bd;
        }
    </style>

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navigation --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('contacts.index') }}">
                <i class="bi bi-person-lines-fill me-2"></i>Contacts
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarMain" aria-controls="navbarMain"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contacts.index') ? 'active' : '' }}"
                           href="{{ route('contacts.index') }}">
                            <i class="bi bi-list-ul me-1"></i>All Contacts
                        </a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contacts.create') ? 'active' : '' }}"
                           href="{{ route('contacts.create') }}">
                            <i class="bi bi-person-plus me-1"></i>New Contact
                        </a>
                    </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @guest
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}"
                           href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    @endguest

                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
    <div class="container mt-3">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
    </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-grow-1 py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-3 mt-auto">
        <div class="container text-center">
            <small>Contact Management &copy; {{ date('Y') }}</small>
        </div>
    </footer>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmF8kuxRqHMN+Ir/qQJGVIjECib2"
            crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
