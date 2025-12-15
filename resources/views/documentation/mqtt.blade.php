<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MQTT Protocol Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #22c55e;
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
            padding: 2rem 0;
            color: #fff;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        h1,
        h2,
        h3 {
            color: #fff;
        }

        h2 {
            color: var(--primary-green);
            margin-top: 1.5rem;
        }

        .table {
            color: rgba(255, 255, 255, 0.9);
        }

        .table thead th {
            background: rgba(20, 83, 45, 0.8);
            color: #86efac;
            border-bottom: 1px solid var(--glass-border);
        }

        .table tbody td {
            border-bottom: 1px solid var(--glass-border);
        }

        pre {
            border-radius: 12px;
        }

        .alert-warning-custom {
            background: rgba(250, 204, 21, 0.2);
            border: 1px solid rgba(250, 204, 21, 0.3);
            color: #fde047;
            border-radius: 12px;
        }

        .alert-info-custom {
            background: rgba(14, 165, 233, 0.2);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #7dd3fc;
            border-radius: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="glass-card">
            <h1><i class="bi bi-wifi me-2"></i>MQTT Protocol Documentation</h1>
            <p class="text-white-50">SmartAgri IoT - Cara Kerja MQTT di Web Application</p>
        </div>

        <!-- How Web Identifies Device -->
        <div class="glass-card">
            <h2><i class="bi bi-search me-2"></i>Cara Web Mengenali Device</h2>

            <div class="alert alert-warning-custom mb-4">
                <h5><i class="bi bi-exclamation-triangle me-2"></i>PENTING!</h5>
                <p class="mb-0">Web mengenali device berdasarkan <strong>MQTT Topic</strong>, BUKAN token!</p>
            </div>

            <p class="text-white-50">Urutan identifikasi device oleh MqttListener:</p>
            <ol class="text-white">
                <li><strong>Pertama:</strong> Cari device berdasarkan <code>mqtt_topic</code> yang di-publish</li>
                <li><strong>Kedua (fallback):</strong> Jika tidak ketemu, cari berdasarkan <code>token</code> di payload
                    JSON</li>
            </ol>

            <pre><code class="language-php">// MqttListener.php - processMessage()
$device = Device::where('mqtt_topic', $topic)->first();  // PRIMARY

if (!$device && isset($data['token'])) {
    $device = Device::where('token', $data['token'])->first();  // FALLBACK
}</code></pre>
        </div>

        <!-- Topics Reference -->
        <div class="glass-card">
            <h2><i class="bi bi-diagram-3 me-2"></i>MQTT Topics</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Direction</th>
                        <th>Topic Pattern</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge bg-info">Device → Web</span></td>
                        <td><code>{mqtt_topic}</code><br><small class="text-white-50">Contoh:
                                ngangngong/bopal/aws1</small></td>
                        <td>Device mengirim data sensor ke topic yang diset di Admin Panel</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-success">Web → Device</span></td>
                        <td><code>{mqtt_topic}/control</code><br><small class="text-white-50">Contoh:
                                ngangngong/bopal/aws1/control</small></td>
                        <td>Web mengirim perintah kontrol output ke device</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-info">Device → Web</span></td>
                        <td><code>devices/{token}/status</code></td>
                        <td>Device mengirim status output + jadwal (device-as-master)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sensor Data Format -->
        <div class="glass-card">
            <h2><i class="bi bi-thermometer-half me-2"></i>Format Data Sensor (Device → Web)</h2>
            <p class="text-white-50">Device mengirim JSON ke topic yang diset di Admin (contoh:
                <code>ngangngong/bopal/aws1</code>)</p>

            <pre><code class="language-json">{
    "temperature_1": 28.5,
    "temperature_2": 29.0,
    "humidity_1": 65.0,
    "humidity_2": 68.0,
    "soil_moisture": 45.0,
    "light_intensity": 800.0
}</code></pre>

            <div class="alert alert-info-custom mt-3">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Catatan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Nama sensor (key) harus sama persis dengan yang dikonfigurasi di Admin Panel</li>
                    <li>Token TIDAK diperlukan jika topic sudah sesuai</li>
                    <li>Data akan disimpan ke tabel database device secara otomatis</li>
                </ul>
            </div>
        </div>

        <!-- Output Control Format -->
        <div class="glass-card">
            <h2><i class="bi bi-toggle-on me-2"></i>Format Kontrol Output (Web → Device)</h2>
            <p class="text-white-50">Web mengirim perintah ke topic <code>{mqtt_topic}/control</code></p>

            <h5 class="mt-4">Manual Control</h5>
            <pre><code class="language-json">{
    "type": "manual_control",
    "output_name": "pump_1",
    "value": 1,
    "token": "pmGQfWN4WyjK2eu1",
    "timestamp": "2025-12-14T12:00:00+07:00"
}</code></pre>

            <h5 class="mt-4">Automation Config</h5>
            <pre><code class="language-json">{
    "type": "automation_config",
    "output_name": "fan_1",
    "automation_type": "time",
    "enabled": true,
    "days": [1, 2, 3, 4, 5],
    "on_time": "06:00",
    "off_time": "18:00",
    "value": 1
}</code></pre>
        </div>

        <!-- Device Status Format -->
        <div class="glass-card">
            <h2><i class="bi bi-broadcast me-2"></i>Format Device Status (Device-as-Master)</h2>
            <p class="text-white-50">Device mengirim status ke topic <code>devices/{token}/status</code></p>

            <div class="alert alert-warning-custom mb-3">
                <i class="bi bi-robot me-1"></i>
                <strong>Device-as-Master:</strong> Device menyimpan semua jadwal dan state. Web hanya menampilkan!
            </div>

            <pre><code class="language-json">{
    "token": "pmGQfWN4WyjK2eu1",
    "outputs": {
        "pump_1": {"value": 0, "label": "Pompa Air"},
        "fan_1": {"value": 1, "label": "Kipas"},
        "valve_1": {"value": 0, "label": "Valve"}
    },
    "schedules": [
        {"id": 1, "output": "fan_1", "type": "time", "on": "06:00", "off": "18:00", "enabled": true}
    ],
    "sensors": {
        "temperature_1": 28.5,
        "humidity_1": 65.0
    },
    "timestamp": "2025-12-14T12:00:00+07:00"
}</code></pre>

            <p class="text-white-50 mt-3"><em>⚠️ Status data TIDAK disimpan ke database, hanya ditampilkan real-time di
                    console.</em></p>
        </div>

        <!-- Testing -->
        <div class="glass-card">
            <h2><i class="bi bi-terminal me-2"></i>Testing</h2>

            <h5>1. Jalankan MQTT Listener</h5>
            <pre><code class="language-bash">php artisan mqtt:listen --host=broker.hivemq.com</code></pre>

            <h5 class="mt-3">2. Kirim Data Sensor (Python)</h5>
            <pre><code class="language-bash">python dummy_sender_mqtt.py</code></pre>

            <h5 class="mt-3">3. Kirim Device Status (Python)</h5>
            <pre><code class="language-bash">python dummy_device_status_mqtt.py</code></pre>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('documentation.esp32') }}" class="btn btn-outline-light">
                <i class="bi bi-cpu me-1"></i> ESP32 Setup Guide
            </a>
            <a href="{{ route('admin.devices.index') }}" class="btn btn-outline-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Admin
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
</body>

</html>