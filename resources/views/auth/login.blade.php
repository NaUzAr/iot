<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Smart Agriculture</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-green: #22c55e;
            --dark-green: #166534;
            --light-green: #86efac;
            --sky-blue: #0ea5e9;
            --primary-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #0ea5e9 100%);
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 80%, rgba(34, 197, 94, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.3) 0%, transparent 50%);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            border-radius: 24px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .login-title {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .form-label {
            color: #86efac;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-green);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            padding: 0.85rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.4);
            color: #fff;
        }

        .alert-danger-custom {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 12px;
        }

        .link-green {
            color: #86efac;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .link-green:hover {
            color: #22c55e;
        }

        .text-muted-light {
            color: rgba(255, 255, 255, 0.5);
        }

        .divider {
            border-top: 1px solid var(--glass-border);
        }
    </style>
</head>

<body>
    <div class="bg-animation"></div>

    <div class="login-card">
        <div class="text-center mb-4">
            <div class="logo-icon">
                <i class="bi bi-tree-fill text-white"></i>
            </div>
            <h4 class="login-title">Selamat Datang!</h4>
            <p class="login-subtitle">Login ke Smart Agriculture System</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger-custom mb-4">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.perform') }}{{ request('pwa') ? '?pwa=1' : '' }}" method="POST">
            @csrf
            @if(request('pwa'))
                <input type="hidden" name="pwa" value="1">
            @endif

            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-person me-1"></i>Username
                </label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}"
                    placeholder="Masukkan username" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Password
                </label>
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Masukkan password" required>
            </div>

            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-gradient">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="text-muted-light small mb-2">Belum punya akun?</p>
            <a href="{{ route('register') }}" class="link-green">Buat Akun Baru</a>

            @if(!request('pwa') && !session('is_pwa'))
                <div class="mt-4 pt-3 divider">
                    <a href="{{ route('home') }}" class="text-muted-light small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.pwa-scripts')
</body>

</html>