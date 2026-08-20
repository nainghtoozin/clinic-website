<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? '') ?: setting('site.site_name') ?: 'Clinic' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Apply saved auth theme before first paint (no flash).
        (function () {
            try {
                var t = localStorage.getItem('auth-theme') || 'light';
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
        window.toggleAuthTheme = function () {
            var el = document.documentElement;
            var next = el.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            el.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('auth-theme', next); } catch (e) {}
        };
    </script>

    <style>
        [x-cloak] { display: none !important; }

        /* ---- Auth page theme palette ---- */
        .auth-page {
            --auth-bg: linear-gradient(150deg, #eef1fb 0%, #e2e8fb 100%);
            --auth-card-bg: #ffffff;
            --auth-card-border: rgba(79, 70, 229, 0.09);
            --auth-input-bg: #ffffff;
            --auth-text: #1f2437;
            --auth-muted: #6b6f8d;
            --auth-brand-title: #262a4e;
            --auth-brand-bg: linear-gradient(165deg, #e9ecff 0%, #dde2ff 100%);
            --auth-float-bg: rgba(255, 255, 255, 0.78);
            --auth-grid-dot: rgba(79, 70, 229, 0.10);
            --auth-shadow: 0 30px 60px -22px rgba(23, 27, 55, 0.28);
        }

        [data-bs-theme="dark"] .auth-page {
            --auth-bg: linear-gradient(150deg, #0e1120 0%, #161b2e 100%);
            --auth-card-bg: #1c1f2e;
            --auth-card-border: #2a2e42;
            --auth-input-bg: #232740;
            --auth-text: #eef0fa;
            --auth-muted: #a5abc6;
            --auth-brand-title: #f0f2fc;
            --auth-brand-bg: linear-gradient(165deg, #1a1f36 0%, #232a47 100%);
            --auth-float-bg: rgba(35, 39, 64, 0.7);
            --auth-grid-dot: rgba(165, 180, 252, 0.08);
            --auth-shadow: 0 30px 60px -22px rgba(0, 0, 0, 0.55);
        }

        body.auth-page {
            min-height: 100vh;
            margin: 0;
            font-family: 'Roboto', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--auth-bg);
            color: var(--auth-text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        /* ---- Theme toggle ---- */
        .auth-theme-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid var(--auth-card-border);
            background: var(--auth-card-bg);
            color: var(--auth-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px -6px rgba(23, 27, 55, 0.35);
            font-size: 1.05rem;
            cursor: pointer;
        }
        .auth-theme-toggle .bi-sun { display: none; }
        [data-bs-theme="dark"] .auth-theme-toggle .bi-moon-stars { display: none; }
        [data-bs-theme="dark"] .auth-theme-toggle .bi-sun { display: inline-block; }

        /* ---- Shell ---- */
        .auth-shell {
            width: 100%;
            max-width: 1080px;
            min-height: min(640px, calc(100vh - 2.5rem));
            display: grid;
            grid-template-columns: 1fr;
            background: var(--auth-card-bg);
            border: 1px solid var(--auth-card-border);
            border-radius: 26px;
            box-shadow: var(--auth-shadow);
            overflow: hidden;
        }

        /* ---- Brand panel ---- */
        .auth-brand {
            position: relative;
            padding: 2.75rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
            background: var(--auth-brand-bg);
            color: var(--auth-brand-title);
        }
        .auth-brand-grid {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, var(--auth-grid-dot) 1px, transparent 1px);
            background-size: 22px 22px;
            -webkit-mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent);
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent);
        }
        .auth-brand-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(46px);
            opacity: .55;
            pointer-events: none;
        }
        .auth-brand-blob {
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4), transparent 70%);
            top: -70px;
            right: -70px;
        }
        .auth-brand-blob-2 {
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.35), transparent 70%);
            bottom: -50px;
            left: -50px;
        }
        .auth-brand-inner {
            position: relative;
            z-index: 1;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-bottom: 2.25rem;
        }
        .auth-logo-mark {
            width: 48px;
            height: 48px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: #fff;
            background: linear-gradient(135deg, var(--clinic-primary), #7c3aed);
            box-shadow: 0 10px 22px -8px rgba(79, 70, 229, 0.55);
            flex: 0 0 auto;
        }
        .auth-logo-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.2rem;
            line-height: 1.2;
            color: var(--auth-brand-title);
        }
        .auth-logo-tag {
            font-size: .72rem;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--auth-muted);
        }

        .auth-brand-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.75rem;
            font-weight: 600;
            line-height: 1.25;
            color: var(--auth-brand-title);
            margin: 0 0 .8rem;
        }
        .auth-brand-sub {
            color: var(--auth-muted);
            font-size: .95rem;
            line-height: 1.6;
            margin: 0 0 2rem;
            max-width: 42ch;
        }

        .auth-brand-cards {
            display: flex;
            flex-direction: column;
            gap: .7rem;
        }
        .auth-float-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .85rem 1rem;
            border-radius: 15px;
            background: var(--auth-float-bg);
            border: 1px solid var(--auth-card-border);
            box-shadow: 0 12px 26px -14px rgba(23, 27, 55, 0.35);
            backdrop-filter: blur(6px);
        }
        .auth-float-card > i {
            font-size: 1.15rem;
            color: var(--clinic-primary);
            width: 24px;
            text-align: center;
        }
        .auth-float-card strong {
            display: block;
            font-size: .9rem;
            color: var(--auth-text);
        }
        .auth-float-card span {
            font-size: .78rem;
            color: var(--auth-muted);
        }

        /* ---- Form panel ---- */
        .auth-form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
        }
        .auth-mobile-logo {
            display: none;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            margin-bottom: 1.4rem;
            color: var(--auth-brand-title);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        .auth-mobile-logo .bi-hospital {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--clinic-primary), #7c3aed);
            color: #fff;
            font-size: 1.1rem;
        }

        .auth-card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.4rem;
            color: var(--auth-text);
            margin: 0 0 .25rem;
        }
        .auth-card-sub {
            color: var(--auth-muted);
            font-size: .9rem;
            margin: 0 0 1.5rem;
        }
        .auth-card-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: var(--auth-muted);
            font-size: .75rem;
        }

        /* ---- Auth form controls (theme-aware) ---- */
        .auth-card .form-control {
            border-radius: 11px;
            padding: .7rem .95rem;
            background: var(--auth-input-bg);
        }
        .auth-card .input-group-text {
            background: var(--auth-input-bg);
            border-color: var(--auth-card-border);
            color: var(--auth-muted);
        }
        .auth-card .form-label {
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: .4rem;
        }
        .auth-card .form-check-input:checked {
            background-color: var(--clinic-primary);
            border-color: var(--clinic-primary);
        }
        .auth-card .btn-primary {
            border-radius: 11px;
            padding: .75rem 1rem;
            font-weight: 600;
        }
        .auth-card a {
            color: var(--clinic-primary);
        }
        .auth-card a:hover {
            color: var(--clinic-primary-dark);
        }

        /* ---- Responsive ---- */
        @media (min-width: 992px) {
            .auth-shell {
                grid-template-columns: 1.05fr 1fr;
            }
        }
        @media (max-width: 991.98px) {
            .auth-shell {
                max-width: 460px;
                min-height: 0;
            }
            .auth-brand {
                display: none;
            }
            .auth-mobile-logo {
                display: flex;
            }
        }
    </style>
</head>
<body class="auth-page">

    <button type="button" class="auth-theme-toggle" onclick="toggleAuthTheme()" aria-label="Toggle light/dark theme" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
        <i class="bi bi-sun"></i>
    </button>

    <div class="auth-shell">
        {{-- Brand / visual panel --}}
        <div class="auth-brand">
            <div class="auth-brand-grid"></div>
            <div class="auth-brand-blob"></div>
            <div class="auth-brand-blob auth-brand-blob-2"></div>
            <div class="auth-brand-inner">
                <div class="auth-logo">
                    <span class="auth-logo-mark"><i class="bi bi-hospital"></i></span>
                    <div>
                        <div class="auth-logo-name">{{ setting('site.site_name') ?: 'Clinic' }}</div>
                        <div class="auth-logo-tag">Clinic Management System</div>
                    </div>
                </div>
                <h1 class="auth-brand-title">Care that connects your clinic, staff and patients.</h1>
                <p class="auth-brand-sub">Appointments, consultations, prescriptions, billing and inventory &mdash; all in one secure, modern workspace.</p>
                <div class="auth-brand-cards">
                    <div class="auth-float-card">
                        <i class="bi bi-calendar-check"></i>
                        <div><strong>Smart scheduling</strong><span>Appointments, queue &amp; consultations</span></div>
                    </div>
                    <div class="auth-float-card">
                        <i class="bi bi-capsule"></i>
                        <div><strong>Batch-aware stock</strong><span>Inventory, expiry &amp; dispensing</span></div>
                    </div>
                    <div class="auth-float-card">
                        <i class="bi bi-shield-check"></i>
                        <div><strong>Secure access</strong><span>Role-based permissions</span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form / card panel --}}
        <div class="auth-form-panel">
            <div class="auth-card">
                <div class="auth-mobile-logo">
                    <i class="bi bi-hospital"></i>
                    <span>{{ setting('site.site_name') ?: 'Clinic' }}</span>
                </div>
                {{ $slot }}
                <p class="auth-card-footer">{{ setting('site.site_name') ?: 'Clinic' }} &middot; &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>

</body>
</html>
