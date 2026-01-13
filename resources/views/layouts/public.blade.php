<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Clinic App') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', system-ui;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
        }

        .card-hover {
            transition: .3s;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, .08);
        }

        .btn-rounded {
            border-radius: 50px;
            padding: 10px 22px;
        }
    </style>
</head>

<body class="bg-light">

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="/">
                🏥 Modern Clinic
            </a>

            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav mx-auto gap-lg-3">
                    <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#doctors">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#products">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="#blogs">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>

                <div class="d-flex align-items-center gap-2">

                    <a href="{{ route('cart.index') }}" class="position-relative text-decoration-none me-4">
                        <i class="bi bi-cart fs-4"></i>

                        @if (session('cart'))
                            <span class="badge bg-danger position-absolute top-0 start-100">
                                {{ count(session('cart')) }}
                            </span>
                        @endif
                    </a>

                    {{-- Book Appointment Button (Public) --}}
                    <a href="#appointment" class="btn btn-primary btn-rounded">
                        Book Appointment
                    </a>

                    {{-- Guest --}}
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="btn btn-primary">
                            Register
                        </a>
                    @endguest

                    {{-- Auth User --}}
                    @auth
                        <div class="dropdown ms-2">
                            <a class="dropdown-toggle text-decoration-none fw-semibold" href="#" role="button"
                                data-bs-toggle="dropdown">

                                {{ Auth::user()->name }}
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                {{-- Dashboard --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                    </a>
                                </li>

                                {{-- Appointment List --}}
                                @notPatient
                                    <li>
                                        <a class="dropdown-item" href="{{ route('appointments.index') }}">
                                            My Appointments
                                        </a>
                                    </li>
                                @endnotPatient

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                {{-- Logout --}}
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>

                            </ul>
                        </div>
                    @endauth

                </div>

            </div>
        </div>
    </nav>

    {{-- Page Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <small class="opacity-75">
                © 2025 Modern Clinic. All rights reserved.
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>




</body>

</html>
