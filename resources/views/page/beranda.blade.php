<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#059669">
    <meta name="description" content="SmartAgri IoT - Sistem monitoring pertanian cerdas berbasis IoT">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SmartAgri">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <title>Smart Agriculture - IoT Monitoring System</title>


    <style>
        :root {
            /* Agriculture Theme - Green & Sky */
            --primary-green: #22c55e;
            --dark-green: #166534;
            --light-green: #86efac;
            --sky-blue: #0ea5e9;
            --light-sky: #7dd3fc;
            --dark-sky: #0369a1;

            --primary-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #0ea5e9 100%);
            --secondary-gradient: linear-gradient(135deg, #86efac 0%, #22c55e 100%);
            --sky-gradient: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
            --nature-gradient: linear-gradient(135deg, #134e4a 0%, #166534 50%, #14532d 100%);

            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--nature-gradient);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Background - Nature Theme */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 80%, rgba(34, 197, 94, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(134, 239, 172, 0.2) 0%, transparent 40%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -30px) rotate(5deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(-5deg);
            }
        }

        /* Glassmorphism Navbar */
        .navbar-glass {
            background: rgba(20, 83, 45, 0.9) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: #86efac !important;
        }

        .navbar-brand i {
            color: #22c55e;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #86efac !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px;
        }

        /* Hero Section */
        .hero-section {
            padding: 6rem 0;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero-title span {
            background: var(--secondary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        /* Glass Cards */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.4s ease;
        }

        .glass-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(34, 197, 94, 0.3);
            border-color: rgba(34, 197, 94, 0.5);
        }

        /* Feature Cards */
        .feature-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(10px);
            border-color: #22c55e;
        }

        .feature-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .feature-icon-box.temp {
            background: linear-gradient(135deg, #f97316, #facc15);
        }

        .feature-icon-box.rain {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        }

        .feature-icon-box.wind {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .feature-icon-box.wifi {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .feature-title {
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .feature-desc {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin: 0;
        }

        /* Buttons */
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.4);
        }

        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(34, 197, 94, 0.5);
            color: #fff;
        }

        .btn-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            transform: translateY(-3px);
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: var(--secondary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        /* Device Image */
        .device-image-container {
            position: relative;
        }

        .device-image {
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            transition: all 0.5s ease;
        }

        .device-image:hover {
            transform: scale(1.02) rotate(1deg);
        }

        .device-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            background: var(--primary-gradient);
            filter: blur(80px);
            opacity: 0.4;
            z-index: -1;
            border-radius: 50%;
        }

        /* Footer */
        .footer-glass {
            background: rgba(20, 83, 45, 0.9);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--glass-border);
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.6);
            margin: 0;
        }

        .footer-link {
            color: #86efac;
            text-decoration: none;
            font-weight: 600;
        }

        /* Admin Badge */
        .admin-badge {
            background: linear-gradient(135deg, #22c55e 0%, #0ea5e9 100%);
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* Dropdown styling */
        .dropdown-menu {
            background: rgba(20, 83, 45, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.5rem;
        }

        .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 0.6rem 1rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }

        .dropdown-divider {
            border-color: var(--glass-border);
        }

        /* Live Indicator */
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        /* Navbar Toggler - Mobile Hamburger Icon */
        .navbar-toggler {
            padding: 0.5rem;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(134, 239, 172, 0.5);
        }

        /* Mobile Navbar Collapse */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(20, 83, 45, 0.98);
                backdrop-filter: blur(20px);
                border-radius: 12px;
                margin-top: 1rem;
                padding: 1rem;
                border: 1px solid var(--glass-border);
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 8px;
                margin-bottom: 0.25rem;
            }

            .navbar-nav .nav-link:hover {
                background: rgba(34, 197, 94, 0.2);
            }

            .navbar-nav .dropdown-menu {
                background: rgba(15, 60, 35, 0.95);
                border: none;
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .navbar-nav .btn {
                width: 100%;
                margin-top: 0.5rem;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-section {
                padding: 3rem 0;
            }

            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="bg-animation"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-tree-fill me-2"></i>SmartAgri
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('home') }}">
                            <i class="bi bi-house-fill me-1"></i> Beranda
                        </a>
                    </li>

                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('monitoring.index') }}">
                                <i class="bi bi-graph-up-arrow me-1"></i> Monitoring
                            </a>
                        </li>

                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear-fill me-1"></i> Admin Panel
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.devices.index') }}"><i
                                                class="bi bi-cpu me-2"></i>Manage Devices</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.device.create') }}"><i
                                                class="bi bi-plus-circle me-2"></i>Tambah Device</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mqtt-tester.index') }}"><i
                                                class="bi bi-broadcast me-2"></i>MQTT Tester</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                data-bs-toggle="dropdown">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                    style="width: 32px; height: 32px; background: var(--primary-gradient);">
                                    <i class="bi bi-person-fill text-white"></i>
                                </div>
                                {{ Auth::user()->name }}
                                @if(Auth::user()->role === 'admin')
                                    <span class="admin-badge ms-2">Admin</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile Saya</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-gradient btn-sm px-4" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" style="margin-top: 80px;">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="live-indicator mb-4">
                        <span class="live-dot"></span>
                        Live Monitoring Active
                    </div>
                    <h1 class="hero-title">
                        Smart <span>Agriculture</span> IoT Monitoring
                    </h1>
                    <p class="hero-subtitle">
                        Sistem pemantauan pertanian cerdas berbasis IoT. Pantau kondisi lahan, cuaca, dan
                        tanaman secara real-time untuk hasil panen yang optimal.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="@auth {{ route('monitoring.index') }} @else {{ route('login') }} @endauth"
                            class="btn btn-gradient">
                            <i class="bi bi-graph-up-arrow me-2"></i>Lihat Data Live
                        </a>
                        <a href="#contact" class="btn btn-glass">
                            <i class="bi bi-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Product Carousel -->
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- AWS Slide -->
                            <div class="carousel-item active">
                                <div class="device-image-container text-center">
                                    <div class="device-glow"></div>
                                    <img src="https://www.renkeer.com/wp-content/uploads/2021/06/weather-station-3-600x600.jpg"
                                        class="img-fluid device-image" alt="AWS Device" style="max-width: 400px;">
                                    <div class="glass-card mt-3 p-3 mx-auto" style="max-width: 400px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="text-white fw-bold mb-0">☁️ AWS</h5>
                                            <span class="badge bg-info">Outdoor</span>
                                        </div>
                                        <p class="text-white-50 small mb-0">Automatic Weather Station - Monitoring cuaca
                                            luar ruangan</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Smart GH Slide -->
                            <div class="carousel-item">
                                <div class="device-image-container text-center">
                                    <div class="device-glow" style="background: var(--secondary-gradient);"></div>
                                    <img src="https://www.renkeer.com/wp-content/uploads/2021/06/weather-station-3-600x600.jpg"
                                        class="img-fluid device-image" alt="Smart Greenhouse" style="max-width: 400px;">
                                    <div class="glass-card mt-3 p-3 mx-auto" style="max-width: 400px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="text-white fw-bold mb-0">🌱 Smart GH</h5>
                                            <span class="badge bg-success">Indoor</span>
                                        </div>
                                        <p class="text-white-50 small mb-0">Smart Greenhouse - Monitoring rumah kaca &
                                            hidroponik</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Carousel Indicators -->
                        <div class="carousel-indicators" style="bottom: -40px;">
                            <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="0"
                                class="active"></button>
                            <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="1"></button>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="glass-card">
                <div class="row">
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Monitoring Aktif</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Jenis Sensor</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime Server</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">
                            <1s< /div>
                                <div class="stat-label">Response Time</div>
                        </div>
                    </div>
    </section>

    <!-- Footer -->
    <footer class="footer-glass">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 text-center">
                    <p class="footer-text mb-0">
                        © 2025 <a href="#" class="footer-link">Smart Agriculture</a> - Tim Engineering IoT Pertanian
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered:', registration.scope);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>

</html>