<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Devices - Smart Agriculture</title>
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

        .page-title i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
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

        .btn-glass {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .table-dark-custom {
            background: rgba(20, 83, 45, 0.8) !important;
        }

        .table-dark-custom th {
            color: #86efac;
            font-weight: 600;
            border-bottom: 1px solid var(--glass-border) !important;
            padding: 1rem;
        }

        .table tbody tr {
            background: transparent;
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .table tbody td {
            color: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem;
            vertical-align: middle;
        }

        .badge-type {
            background: var(--sky-gradient);
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
            color: #fff;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .badge-sensor {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            margin: 2px;
            font-size: 0.75rem;
            display: inline-block;
        }

        .badge-output {
            background: rgba(250, 204, 21, 0.2);
            color: #fde047;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            margin: 2px;
            font-size: 0.75rem;
            display: inline-block;
        }

        .badge-token {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-family: monospace;
            padding: 0.35rem 0.6rem;
            border-radius: 8px;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-action-edit {
            background: rgba(250, 204, 21, 0.2);
            color: #facc15;
        }

        .btn-action-edit:hover {
            background: rgba(250, 204, 21, 0.4);
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-action-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .btn-action-delete:hover {
            background: rgba(239, 68, 68, 0.4);
            color: #fff;
            transform: translateY(-2px);
        }

        .alert-success-custom {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            border-radius: 12px;
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.3);
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.6);
            margin-top: 1rem;
        }

        .empty-state a {
            color: #86efac;
        }
    </style>
</head>

<body>
    <div class="bg-animation"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-tree-fill me-2"></i>SmartAgri
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="bi bi-house me-1"></i> Beranda
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0">
                <i class="bi bi-cpu-fill me-2"></i>Device Management
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.mqtt-tester.index') }}" class="btn btn-glass">
                    <i class="bi bi-broadcast me-1"></i> MQTT Tester
                </a>
                <a href="{{ route('admin.device.create') }}" class="btn btn-gradient">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Device
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success-custom mb-4">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-dark-custom">
                        <tr>
                            <th>#</th>
                            <th>Nama Device</th>
                            <th>Tipe</th>
                            <th>Sensors</th>
                            <th>Outputs</th>
                            <th>MQTT Topic</th>
                            <th>Token</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td class="fw-semibold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $device->name }}</div>
                                    <small class="text-white-50">{{ $device->table_name }}</small>
                                </td>
                                <td>
                                    <span class="badge-type">
                                        <i
                                            class="bi {{ $device->type === 'aws' ? 'bi-cloud-sun' : 'bi-flower1' }} me-1"></i>
                                        {{ strtoupper($device->type ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    @if($device->sensors->count() > 0)
                                        @foreach($device->sensors->take(4) as $sensor)
                                            <span class="badge-sensor" title="{{ $sensor->sensor_label }}">
                                                {{ $sensor->sensor_name }}
                                            </span>
                                        @endforeach
                                        @if($device->sensors->count() > 4)
                                            <span class="badge-sensor">+{{ $device->sensors->count() - 4 }}</span>
                                        @endif
                                    @else
                                        <span class="text-white-50">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($device->outputs->count() > 0)
                                        @foreach($device->outputs->take(3) as $output)
                                            <span class="badge-output" title="{{ $output->output_label }}">
                                                <i class="bi bi-toggle-on me-1"></i>{{ $output->output_name }}
                                            </span>
                                        @endforeach
                                        @if($device->outputs->count() > 3)
                                            <span class="badge-output">+{{ $device->outputs->count() - 3 }}</span>
                                        @endif
                                    @else
                                        <span class="text-white-50">-</span>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-info">{{ $device->mqtt_topic }}</code>
                                </td>
                                <td>
                                    <span class="badge-token">{{ $device->token }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.device.edit', $device->id) }}"
                                        class="btn-action btn-action-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.device.destroy', $device->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('⚠️ BAHAYA: Menghapus device akan MENGHAPUS TABEL {{ $device->table_name }} secara permanen!\n\nLanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-delete" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <p>Belum ada device. <a href="{{ route('admin.device.create') }}">Tambah device
                                                pertama!</a></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MQTT DOCUMENTATION SECTION -->
        <div class="glass-card mt-4 p-4">
            <h4 class="text-white mb-4">
                <i class="bi bi-book me-2" style="color: #86efac;"></i>Dokumentasi MQTT
            </h4>

            <div class="row g-4">
                <!-- Input Format -->
                <div class="col-lg-6">
                    <div class="p-3 rounded" style="background: rgba(0,0,0,0.2);">
                        <h6 class="text-info mb-3"><i class="bi bi-arrow-up-circle me-2"></i>Format JSON untuk Input
                            (Sensor Data)</h6>
                        <p class="text-white-50 small mb-2">Device mengirim data sensor ke server dengan format:</p>
                        <pre class="text-white mb-0 p-3 rounded"
                            style="background: rgba(0,0,0,0.3); font-size: 0.8rem; overflow-x: auto;"><code>{
    "token": "YOUR_DEVICE_TOKEN",
    "temperature": 28.5,
    "humidity": 65.2,
    "sensor_name": value
}</code></pre>
                        <div class="mt-2 small text-white-50">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>token</strong> wajib ada untuk autentikasi device.
                        </div>
                    </div>
                </div>

                <!-- Output Format -->
                <div class="col-lg-6">
                    <div class="p-3 rounded" style="background: rgba(0,0,0,0.2);">
                        <h6 style="color: #fde047;" class="mb-3"><i class="bi bi-arrow-down-circle me-2"></i>Format JSON
                            untuk Output (Control Commands)</h6>
                        <p class="text-white-50 small mb-2">Server mengirim perintah kontrol ke device dengan format:
                        </p>
                        <pre class="text-white mb-0 p-3 rounded"
                            style="background: rgba(0,0,0,0.3); font-size: 0.8rem; overflow-x: auto;"><code>{
    "token": "YOUR_DEVICE_TOKEN",
    "action": "set_output",
    "outputs": {
        "relay_1": true,
        "pump": false,
        "motor": 75
    }
}</code></pre>
                        <div class="mt-2 small text-white-50">
                            <i class="bi bi-info-circle me-1"></i>
                            Boolean: <code>true/false</code>, Number: <code>0-100</code> atau sesuai range.
                        </div>
                    </div>
                </div>

                <!-- MQTT Configuration -->
                <div class="col-lg-6">
                    <div class="p-3 rounded" style="background: rgba(0,0,0,0.2);">
                        <h6 class="text-success mb-3"><i class="bi bi-gear me-2"></i>Konfigurasi MQTT Broker</h6>
                        <table class="table table-sm mb-0" style="color: rgba(255,255,255,0.8);">
                            <tr>
                                <td class="border-0 py-1"><strong>Broker (Public):</strong></td>
                                <td class="border-0 py-1"><code class="text-info">broker.hivemq.com</code></td>
                            </tr>
                            <tr>
                                <td class="border-0 py-1"><strong>Port:</strong></td>
                                <td class="border-0 py-1"><code>1883</code> (normal) / <code>8883</code> (SSL)</td>
                            </tr>
                            <tr>
                                <td class="border-0 py-1"><strong>Topic Input:</strong></td>
                                <td class="border-0 py-1"><code class="text-info">[mqtt_topic dari device]</code></td>
                            </tr>
                            <tr>
                                <td class="border-0 py-1"><strong>Topic Output:</strong></td>
                                <td class="border-0 py-1"><code class="text-warning">[mqtt_topic]/control</code></td>
                            </tr>
                            <tr>
                                <td class="border-0 py-1"><strong>QoS:</strong></td>
                                <td class="border-0 py-1"><code>1</code> (at least once)</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Python Example -->
                <div class="col-lg-6">
                    <div class="p-3 rounded" style="background: rgba(0,0,0,0.2);">
                        <h6 style="color: #a78bfa;" class="mb-3"><i class="bi bi-filetype-py me-2"></i>Contoh Python
                            (paho-mqtt)</h6>
                        <pre class="text-white mb-0 p-3 rounded"
                            style="background: rgba(0,0,0,0.3); font-size: 0.75rem; overflow-x: auto;"><code>import paho.mqtt.client as mqtt
import json

client = mqtt.Client()
client.connect("broker.hivemq.com", 1883)

# Kirim data sensor
data = {
    "token": "YOUR_TOKEN",
    "temperature": 28.5,
    "humidity": 65
}
client.publish("sensor/device/data", json.dumps(data))</code></pre>
                        <div class="mt-2 small text-white-50">
                            <i class="bi bi-terminal me-1"></i>
                            Install: <code>pip install paho-mqtt</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="alert mt-4 mb-0"
                style="background: rgba(14, 165, 233, 0.2); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 12px;">
                <h6 class="text-info mb-2"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                <ul class="mb-0 small text-white" style="padding-left: 1.5rem;">
                    <li>Gunakan file <code>dummy_sender_mqtt.py</code> di root project untuk testing</li>
                    <li>Jalankan <code>php artisan mqtt:listen --host=broker.hivemq.com</code> untuk menerima data</li>
                    <li>Pastikan <strong>token</strong> device sudah benar agar data tersimpan ke database</li>
                    <li>Nama sensor/output di JSON harus sama persis dengan yang dikonfigurasi di device</li>
                </ul>

                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('documentation.esp32') }}" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-cpu me-1"></i> Panduan ESP32
                    </a>
                    <a href="{{ route('documentation.mqtt') }}" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-book me-1"></i> MQTT Protocol Docs
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>