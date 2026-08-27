<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Clinic Website') }}</title>

    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">


    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">


    <!-- =======================================================
  * Template Name: MediNest
  * Template URL: https://bootstrapmade.com/medinest-bootstrap-hospital-template/
  * Updated: Aug 11 2025 with Bootstrap v5.3.7
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container position-relative d-flex align-items-center justify-content-between">

            <a href="{{ route('public.index') }}" class="logo d-flex align-items-center me-auto me-xl-0">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <!-- <img src="assets/img/logo.webp" alt=""> -->
                <h1 class="sitename">{{ setting('site.site_name') }}</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="{{ route('public.index') }}" class="active">Home</a></li>
                    <li><a href="{{ route('public.about') }}">About</a></li>
                    <li><a href="{{ route('public.department') }}">Departments</a></li>
                    <li><a href="{{ route('public.services') }}">Services</a></li>
                    <li><a href="{{ route('public.doctor-list') }}">Doctors</a></li>
                    <li><a href="{{ route('public.faq') }}">FAQ</a></li>
                    <li><a href="{{ route('public.contact') }}">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="btn-getstarted" href="{{ route('public.appointment.create') }}">Appointment</a>

        </div>
    </header>

    <main class="main">
        {{ $slot }}
    </main>

    <footer id="footer" class="footer position-relative">

        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="{{ route('public.index') }}" class="logo d-flex align-items-center">
                        <span class="sitename">{{ setting('site.site_name') }}</span>
                    </a>
                    <div class="footer-contact pt-3">
                        <p>{{ setting('site.address') ?: 'Clinic Address' }}</p>
                        <p class="mt-3"><strong>Phone:</strong> <span>{{ setting('site.phone') ?: 'Clinic Phone' }}</span></p>
                        <p><strong>Email:</strong> <span>{{ setting('site.email') ?: 'info@clinic.com' }}</span></p>
                    </div>
                    <div class="social-links d-flex mt-4">
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Useful Links</h4>
                    <ul>
                        <li><a href="{{ route('public.index') }}">Home</a></li>
                        <li><a href="{{ route('public.about') }}">About us</a></li>
                        <li><a href="{{ route('public.services') }}">Services</a></li>
                        <li><a href="{{ route('public.terms') }}">Terms of service</a></li>
                        <li><a href="{{ route('public.privacy') }}">Privacy policy</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Our Services</h4>
                    <ul>
                        <li><a href="{{ route('public.services') }}">General Consultation</a></li>
                        <li><a href="{{ route('public.services') }}">Specialist Care</a></li>
                        <li><a href="{{ route('public.services') }}">Diagnostics</a></li>
                        <li><a href="{{ route('public.services') }}">Pharmacy</a></li>
                        <li><a href="{{ route('public.services') }}">Health Checkup</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ route('public.doctor-list') }}">Our Doctors</a></li>
                        <li><a href="{{ route('public.department') }}">Departments</a></li>
                        <li><a href="{{ route('public.appointment.create') }}">Book Appointment</a></li>
                        <li><a href="{{ route('public.faq') }}">FAQ</a></li>
                        <li><a href="{{ route('public.contact') }}">Contact</a></li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>&copy; {{ date('Y') }} <strong>{{ setting('site.site_name') ?: config('app.name') }}</strong>. All Rights Reserved.</p>
        </div>
        </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>
