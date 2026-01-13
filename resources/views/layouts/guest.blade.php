<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Social App') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont;
            background: black;
            min-height: 100vh;
        }

        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.15);
            animation: fadeUp .6s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .app-logo {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .app-subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Inputs */
        .form-control {
            border-radius: 12px;
            padding: 12px 14px;
            border: none;
            background: rgba(255, 255, 255, 0.85);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.3);
        }

        /* Buttons */
        .btn-social {
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
        }

        .btn-social:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .divider {
            text-align: center;
            position: relative;
            margin: 20px 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: rgba(255, 255, 255, 0.4);
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 p-md-5 text-white">

                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <div class="app-logo">{{ config('app.name', 'Social App') }}</div>
                        <div class="app-subtitle">Connect. Share. Discover.</div>
                    </div>

                    <!-- Slot -->
                    {{ $slot }}

                </div>
            </div>
        </div>
    </div>
</body>

</html>
