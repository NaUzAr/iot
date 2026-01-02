<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring - Smart Agriculture</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">


    <style>
        :root {
            --primary-green: #22c55e;
            --dark-green: #166534;
            --light-green: #86efac;
            --sky-blue: #0ea5e9;
            --light-sky: #7dd3fc;
            --primary-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #0ea5e9 100%);
            --secondary-gradient: linear-gradient(135deg, #86efac 0%, #22c55e 100%);
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
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 80%, rgba(34, 197, 94, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.2) 0%, transparent 50%);
        }

        .navbar-glass {
            background: rgba(20, 83, 45, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }

        .navbar-brand {
            font-weight: 700;
            color: #86efac !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .nav-link:hover {
            color: #86efac !important;
        }

        .page-title {
            color: #fff;
            font-weight: 700;
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.4);
            color: #fff;
        }

        .device-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .device-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-green);
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.2);
        }

        .device-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .device-name {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .device-type {
            background: rgba(14, 165, 233, 0.2);
            color: var(--light-sky);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .sensor-count {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }

        .btn-view {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-view:hover {
            background: var(--primary-green);
            color: #fff;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
        }

        .alert-success-custom {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            border-radius: 12px;
        }

        .empty-state {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.3);
        }

        .empty-state h5 {
            color: #fff;
            margin-top: 1rem;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>

<body>
    <div class="bg-animation"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass">
        <div class="container">
            <a class="navbar-brand" href="{{ session('is_pwa') ? route('monitoring.index') : route('home') }}">
                <i class="bi bi-tree-fill me-2"></i>SmartAgri
            </a>
            <div class="navbar-nav ms-auto">
                @if(!session('is_pwa'))
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="bi bi-house me-1"></i> Beranda
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link" style="color: rgba(255,255,255,0.8);">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0">
                <i class="bi bi-graph-up-arrow me-2"></i>Monitoring Devices
            </h2>
            <a href="{{ route('monitoring.create') }}" class="btn btn-gradient">
                <i class="bi bi-plus-lg me-1"></i> Tambah Device
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success-custom mb-4">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($userDevices->count() > 0)
            <div class="row g-4">
                @foreach($userDevices as $userDevice)
                    <div class="col-md-6 col-lg-4">
                        <div class="device-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="device-icon">
                                    <i
                                        class="bi {{ $userDevice->device->type === 'aws' ? 'bi-cloud-sun-fill' : 'bi-flower1' }} text-white"></i>
                                </div>
                                <form action="{{ route('monitoring.destroy', $userDevice->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus device ini dari monitoring?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                            <h5 class="device-name">{{ $userDevice->custom_name }}</h5>
                            <span class="device-type">
                                {{ strtoupper($userDevice->device->type ?? 'N/A') }}
                            </span>

                            <p class="sensor-count mb-3">
                                <i class="bi bi-thermometer-half me-1"></i>
                                {{ $userDevice->device->sensors->count() }} Sensor Aktif
                            </p>

                            <a href="{{ route('monitoring.show', $userDevice->id) }}"
                                class="btn-view w-100 d-block text-center">
                                <i class="bi bi-graph-up me-1"></i> Lihat Data
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Belum Ada Device</h5>
                <p>Tambahkan device dengan memasukkan token untuk mulai monitoring.</p>
                <a href="{{ route('monitoring.create') }}" class="btn btn-gradient mt-3">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Device Pertama
                </a>
            </div>
        @endif
    </div>

    @include('partials.pwa-scripts')
</body>

</html>