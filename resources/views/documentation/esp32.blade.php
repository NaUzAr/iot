<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ESP32 MQTT Setup - SmartAgri</title>
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

        pre {
            border-radius: 12px;
            max-height: 500px;
            overflow-y: auto;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: var(--primary-green);
            border-radius: 50%;
            font-weight: 700;
            margin-right: 0.75rem;
        }

        .library-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }

        .library-card i {
            font-size: 2rem;
            color: #86efac;
        }

        .alert-info-custom {
            background: rgba(14, 165, 233, 0.2);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #7dd3fc;
            border-radius: 12px;
        }

        .btn-glass {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            text-decoration: none;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .code-wrapper {
            position: relative;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="glass-card">
            <h1><i class="bi bi-cpu me-2"></i>ESP32 MQTT Setup Guide</h1>
            <p class="text-white-50">Panduan lengkap untuk menghubungkan device ESP32 ke SmartAgri IoT Platform</p>
        </div>

        <!-- Requirements -->
        <div class="glass-card">
            <h2><i class="bi bi-box-seam me-2"></i>Required Libraries</h2>
            <p class="text-white-50">Install library berikut via Arduino Library Manager:</p>

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <div class="library-card">
                        <i class="bi bi-wifi"></i>
                        <h6 class="mt-2 mb-0">PubSubClient</h6>
                        <small class="text-white-50">by Nick O'Leary</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="library-card">
                        <i class="bi bi-braces"></i>
                        <h6 class="mt-2 mb-0">ArduinoJson</h6>
                        <small class="text-white-50">by Benoît Blanchon</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="library-card">
                        <i class="bi bi-thermometer-half"></i>
                        <h6 class="mt-2 mb-0">DHT sensor library</h6>
                        <small class="text-white-50">by Adafruit (optional)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        <div class="glass-card">
            <h2><i class="bi bi-gear me-2"></i>Konfigurasi Device</h2>
            <p class="text-white-50">Ambil informasi berikut dari Admin Panel saat membuat device:</p>

            <div class="alert alert-info-custom">
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Cara Web Mengenali Device</h5>
                <ul class="mb-0">
                    <li><strong>MQTT_TOPIC</strong>: <span class="badge bg-success">PRIMARY</span> - Topic dari Admin
                        Panel (wajib sama persis!)</li>
                    <li><strong>DEVICE_TOKEN</strong>: <span class="badge bg-secondary">OPTIONAL</span> - Fallback jika
                        topic tidak cocok</li>
                    <li><strong>MQTT_BROKER</strong>: <code>broker.hivemq.com</code> (free public broker)</li>
                    <li><strong>MQTT_PORT</strong>: <code>1883</code></li>
                </ul>
                <div class="mt-2 small">
                    <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                    <strong>PENTING:</strong> Web mencari device berdasarkan <strong>MQTT Topic</strong>, BUKAN token!
                </div>
            </div>

            <pre class="mt-3"><code class="language-cpp">// ========= KONFIGURASI DEVICE =========
// MQTT Topic HARUS SAMA PERSIS dengan yang diset di Admin Panel!

const char* MQTT_TOPIC = "ngangngong/bopal/aws1";  // PRIMARY - dari Admin Panel
const char* DEVICE_TOKEN = "pmGQfWN4WyjK2eu1";      // OPTIONAL - fallback saja
const char* mqtt_server = "broker.hivemq.com";
const int mqtt_port = 1883;</code></pre>
        </div>

        <!-- Sensor Code -->
        <div class="glass-card">
            <h2><i class="bi bi-thermometer-half me-2"></i>Kode Mengirim Data Sensor</h2>
            <p class="text-white-50">Kode ESP32 untuk membaca sensor dan mengirim ke MQTT broker:</p>

            <div class="code-wrapper">
                <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyCode('sensorCode')">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
                <pre><code class="language-cpp" id="sensorCode">{{ $sensorCode }}</code></pre>
            </div>
        </div>

        <!-- Output Control Code -->
        <div class="glass-card">
            <h2><i class="bi bi-toggle-on me-2"></i>Kode Kontrol Output + Scheduling</h2>
            <p class="text-white-50">Kode ESP32 untuk menerima perintah dan menyimpan jadwal:</p>

            <div class="code-wrapper">
                <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyCode('outputCode')">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
                <pre><code class="language-cpp" id="outputCode">{{ $outputCode }}</code></pre>
            </div>
        </div>

        <!-- JSON Format -->
        <div class="glass-card">
            <h2><i class="bi bi-braces me-2"></i>Format JSON Data Sensor</h2>
            <p class="text-white-50">Format payload yang harus dikirim ke MQTT topic
                (<code>ngangngong/bopal/aws1</code>):</p>

            <pre><code class="language-json">{
    "temperature_1": 28.5,
    "temperature_2": 29.0,
    "humidity_1": 65.0,
    "humidity_2": 68.0,
    "soil_moisture": 45.0,
    "light_intensity": 800.0
}</code></pre>

            <div class="alert alert-info-custom mt-3">
                <i class="bi bi-lightbulb me-1"></i>
                <strong>Tips:</strong>
                <ul class="mb-0 mt-2">
                    <li>Nama sensor (key) harus sama persis dengan yang dikonfigurasi di Admin Panel!</li>
                    <li>Web mengenali device dari <strong>MQTT Topic</strong>, bukan dari token di payload</li>
                    <li>Token di payload hanya untuk fallback jika topic tidak cocok</li>
                </ul>
            </div>
        </div>

        <!-- Wiring Diagram -->
        <div class="glass-card">
            <h2><i class="bi bi-diagram-3 me-2"></i>Contoh Wiring</h2>

            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-white-50">Sensor Inputs</h5>
                    <table class="table table-sm text-white">
                        <tr>
                            <td>DHT22 Data</td>
                            <td>→ GPIO 4</td>
                        </tr>
                        <tr>
                            <td>Soil Moisture</td>
                            <td>→ GPIO 34 (ADC)</td>
                        </tr>
                        <tr>
                            <td>LDR</td>
                            <td>→ GPIO 35 (ADC)</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="text-white-50">Output Relays</h5>
                    <table class="table table-sm text-white">
                        <tr>
                            <td>Pompa</td>
                            <td>→ GPIO 25</td>
                        </tr>
                        <tr>
                            <td>Kipas</td>
                            <td>→ GPIO 26</td>
                        </tr>
                        <tr>
                            <td>Valve</td>
                            <td>→ GPIO 27</td>
                        </tr>
                        <tr>
                            <td>LED</td>
                            <td>→ GPIO 32</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('documentation.mqtt') }}" class="btn-glass">
                <i class="bi bi-book me-1"></i> MQTT Protocol Docs
            </a>
            <a href="{{ route('admin.devices.index') }}" class="btn-glass">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Admin
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();

        function copyCode(id) {
            const code = document.getElementById(id).textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert('Code copied to clipboard!');
            });
        }
    </script>
</body>

</html>