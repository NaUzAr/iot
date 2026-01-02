<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MQTT Tester - Smart Agriculture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

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

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
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

        .form-control-glass,
        .form-select-glass {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 10px;
        }

        .form-control-glass:focus,
        .form-select-glass:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--primary-green);
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
        }

        .form-control-glass::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-select-glass option {
            background: #134e4a;
            color: #fff;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .nav-tabs-glass {
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-tabs-glass .nav-link {
            color: rgba(255, 255, 255, 0.6);
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        .nav-tabs-glass .nav-link:hover {
            color: #fff;
            border: none;
        }

        .nav-tabs-glass .nav-link.active {
            background: transparent;
            color: #86efac;
            border-bottom: 3px solid #22c55e;
        }

        .sensor-field,
        .output-field {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .badge-sensor {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            font-weight: 500;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .badge-output {
            background: rgba(250, 204, 21, 0.2);
            color: #fde047;
            font-weight: 500;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .result-box {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
        }

        .result-box pre {
            color: #86efac;
            margin: 0;
            font-size: 0.85rem;
        }

        .result-success {
            border-left: 4px solid #22c55e;
        }

        .result-error {
            border-left: 4px solid #ef4444;
        }

        .device-info {
            background: rgba(14, 165, 233, 0.1);
            border: 1px solid rgba(14, 165, 233, 0.3);
            border-radius: 12px;
            padding: 1rem;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.show {
            display: inline-block;
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
                <a class="nav-link" href="{{ route('admin.devices.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="page-title mb-4">
            <i class="bi bi-broadcast me-2" style="color: #86efac;"></i>MQTT Tester
        </h2>

        <div class="row g-4">
            <!-- Device Selection -->
            <div class="col-lg-4">
                <div class="glass-card h-100">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-cpu me-2"></i>Pilih Device
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Pilih dari Daftar Device</label>
                        <select class="form-select form-select-glass" id="deviceSelect">
                            <option value="">-- Pilih Device --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}" data-token="{{ $device->token }}"
                                    data-topic="{{ $device->mqtt_topic }}" data-type="{{ $device->type }}">
                                    {{ $device->name }} ({{ strtoupper($device->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center text-white-50 my-3">— atau —</div>

                    <div class="mb-3">
                        <label class="form-label">Input Token Manual</label>
                        <input type="text" class="form-control form-control-glass" id="manualToken"
                            placeholder="Masukkan device token...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">MQTT Topic</label>
                        <input type="text" class="form-control form-control-glass" id="mqttTopic"
                            placeholder="contoh: smartagri/aws/001">
                    </div>

                    <!-- Device Info Display -->
                    <div class="device-info mt-4" id="deviceInfo" style="display: none;">
                        <h6 class="text-info mb-2"><i class="bi bi-info-circle me-1"></i> Device Info</h6>
                        <div id="deviceInfoContent"></div>
                    </div>
                </div>
            </div>

            <!-- Test Forms -->
            <div class="col-lg-8">
                <div class="glass-card">
                    <ul class="nav nav-tabs nav-tabs-glass mb-4" id="testTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sensorTab">
                                <i class="bi bi-thermometer-half me-1"></i> Sensor Data
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#outputTab">
                                <i class="bi bi-toggle-on me-1"></i> Output Control
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#scheduleTab">
                                <i class="bi bi-calendar-check me-1"></i> Schedule
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#statusTab">
                                <i class="bi bi-question-circle me-1"></i> Request Status
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#customTab">
                                <i class="bi bi-code-slash me-1"></i> Custom JSON
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#monitorTab">
                                <i class="bi bi-activity me-1"></i> Live Monitor
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bulkTab">
                                <i class="bi bi-dice-5 me-1"></i> Bulk Random
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Sensor Data Tab -->
                        <div class="tab-pane fade show active" id="sensorTab">
                            <p class="text-white-50 mb-3">Kirim data sensor ke MQTT (simulasi device mengirim data)</p>

                            <div id="sensorFields">
                                <div class="text-white-50 text-center py-4">
                                    <i class="bi bi-arrow-left-circle me-2"></i>
                                    Pilih device untuk melihat sensor fields
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-gradient" id="sendSensorBtn" disabled>
                                    <i class="bi bi-send me-1"></i> Kirim Sensor Data
                                    <span class="loading-spinner spinner-border spinner-border-sm ms-2"></span>
                                </button>
                                <button type="button" class="btn btn-outline-light" id="randomizeBtn" disabled
                                    onclick="randomizeSensorValues()">
                                    <i class="bi bi-dice-5 me-1"></i> Generate Random
                                </button>
                            </div>
                        </div>

                        <!-- Output Control Tab -->
                        <div class="tab-pane fade" id="outputTab">
                            <p class="text-white-50 mb-3">Kirim perintah kontrol output ke device</p>

                            <div id="outputFields">
                                <div class="text-white-50 text-center py-4">
                                    <i class="bi bi-arrow-left-circle me-2"></i>
                                    Pilih device untuk melihat output fields
                                </div>
                            </div>

                            <button class="btn btn-gradient mt-3" id="sendOutputBtn" disabled>
                                <i class="bi bi-send me-1"></i> Kirim Output Control
                                <span class="loading-spinner spinner-border spinner-border-sm ms-2"></span>
                            </button>
                        </div>

                        <!-- Schedule Tab -->
                        <div class="tab-pane fade" id="scheduleTab">
                            <p class="text-white-50 mb-3">Kirim jadwal ke device (time-based atau sensor-based)</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Output Target</label>
                                    <select class="form-select form-select-glass" id="scheduleOutput">
                                        <option value="">-- Pilih Output --</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipe Schedule</label>
                                    <select class="form-select form-select-glass" id="scheduleType">
                                        <option value="time">Time-based</option>
                                        <option value="sensor">Sensor-based</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Time Schedule Fields -->
                            <div id="timeScheduleFields" class="mt-3">
                                <div class="sensor-field">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Waktu ON</label>
                                            <input type="time" class="form-control form-control-glass" id="timeOn"
                                                value="06:00">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Waktu OFF</label>
                                            <input type="time" class="form-control form-control-glass" id="timeOff"
                                                value="18:00">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Hari (0=Minggu)</label>
                                            <input type="text" class="form-control form-control-glass" id="scheduleDays"
                                                value="1,2,3,4,5" placeholder="1,2,3,4,5">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sensor Schedule Fields -->
                            <div id="sensorScheduleFields" class="mt-3" style="display: none;">
                                <div class="sensor-field">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Sensor</label>
                                            <select class="form-select form-select-glass" id="ruleSensor"></select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Operator</label>
                                            <select class="form-select form-select-glass" id="ruleOperator">
                                                <option value="<">&lt; Kurang dari</option>
                                                <option value=">">&gt; Lebih dari</option>
                                                <option value="<=">≤ Kurang sama dengan</option>
                                                <option value=">=">≥ Lebih sama dengan</option>
                                                <option value="==">= Sama dengan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Threshold</label>
                                            <input type="number" class="form-control form-control-glass"
                                                id="ruleThreshold" value="30">
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Action saat TRUE</label>
                                            <select class="form-select form-select-glass" id="actionTrue">
                                                <option value="1">ON</option>
                                                <option value="0">OFF</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Action saat FALSE</label>
                                            <select class="form-select form-select-glass" id="actionFalse">
                                                <option value="0">OFF</option>
                                                <option value="1">ON</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-gradient mt-3" id="sendScheduleBtn" disabled>
                                <i class="bi bi-send me-1"></i> Kirim Schedule
                                <span class="loading-spinner spinner-border spinner-border-sm ms-2"></span>
                            </button>
                        </div>

                        <!-- Status Request Tab -->
                        <div class="tab-pane fade" id="statusTab">
                            <p class="text-white-50 mb-3">Minta status terkini dari device</p>

                            <div class="sensor-field">
                                <p class="text-white mb-2">Kirim request ke device untuk mendapatkan:</p>
                                <ul class="text-white-50 small">
                                    <li>Status semua output (ON/OFF)</li>
                                    <li>Jadwal yang aktif</li>
                                    <li>Aturan sensor yang terkonfigurasi</li>
                                </ul>
                            </div>

                            <button class="btn btn-gradient mt-3" id="sendStatusBtn" disabled>
                                <i class="bi bi-send me-1"></i> Request Status
                                <span class="loading-spinner spinner-border spinner-border-sm ms-2"></span>
                            </button>
                        </div>

                        <!-- Custom JSON Tab -->
                        <div class="tab-pane fade" id="customTab">
                            <p class="text-white-50 mb-3">Tulis JSON payload custom untuk testing format apapun</p>

                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Custom Topic (opsional, kosongkan untuk pakai topic
                                        device)</label>
                                    <input type="text" class="form-control form-control-glass" id="customTopic"
                                        placeholder="Kosongkan untuk pakai MQTT Topic dari device...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Topic Suffix</label>
                                    <select class="form-select form-select-glass" id="topicSuffix">
                                        <option value="">Tanpa suffix (data topic)</option>
                                        <option value="/control">/control (command topic)</option>
                                        <option value="/status">/status</option>
                                        <option value="/config">/config</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">JSON Payload</label>
                                <textarea class="form-control form-control-glass" id="customJson" rows="12"
                                    style="font-family: monospace; font-size: 0.9rem;" placeholder='{
    "token": "YOUR_TOKEN",
    "key": "value",
    "sensor": 25.5
}'></textarea>
                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-light"
                                        onclick="loadTemplate('sensor')">
                                        <i class="bi bi-file-code me-1"></i> Template Sensor
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light"
                                        onclick="loadTemplate('output')">
                                        <i class="bi bi-file-code me-1"></i> Template Output
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light"
                                        onclick="loadTemplate('schedule')">
                                        <i class="bi bi-file-code me-1"></i> Template Schedule
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light" onclick="formatJson()">
                                        <i class="bi bi-braces me-1"></i> Format JSON
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearJson()">
                                        <i class="bi bi-trash me-1"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <button class="btn btn-gradient mt-2" id="sendCustomBtn">
                                <i class="bi bi-send me-1"></i> Kirim Custom JSON
                                <span class="loading-spinner spinner-border spinner-border-sm ms-2"></span>
                            </button>
                        </div>

                        <!-- Live Monitor Tab -->
                        <div class="tab-pane fade" id="monitorTab">
                            <p class="text-white-50 mb-3">Subscribe ke topic device dan lihat pesan MQTT secara
                                real-time</p>

                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">MQTT Broker (WebSocket)</label>
                                    <input type="text" class="form-control form-control-glass" id="wsBroker"
                                        value="wss://broker.hivemq.com:8884/mqtt"
                                        placeholder="wss://broker.hivemq.com:8884/mqtt">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Topic untuk Subscribe</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-glass" id="subscribeTopic"
                                            placeholder="smartagri/device/# atau topic device...">
                                        <button class="btn btn-outline-light" type="button" onclick="useDeviceTopic()">
                                            <i class="bi bi-cpu"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button class="btn btn-gradient" id="subscribeBtn"
                                            onclick="toggleSubscription()">
                                            <i class="bi bi-play-fill me-1"></i> Start
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-light" onclick="addWildcard('#')">
                                            <i class="bi bi-hash me-1"></i> + Multi-level (#)
                                        </button>
                                        <button class="btn btn-sm btn-outline-light" onclick="addWildcard('+')">
                                            <i class="bi bi-plus me-1"></i> + Single-level (+)
                                        </button>
                                        <button class="btn btn-sm btn-outline-light" onclick="addSuffix('/control')">
                                            /control
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <span id="connectionStatus" class="badge bg-secondary">
                                        <i class="bi bi-circle-fill me-1"></i> Disconnected
                                    </span>
                                    <span id="messageCount" class="badge bg-info ms-2">
                                        0 messages
                                    </span>
                                </div>
                            </div>

                            <!-- Message Log -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">
                                        <i class="bi bi-journal-text me-1"></i> Message Log
                                    </label>
                                    <div class="d-flex gap-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="autoScroll" checked>
                                            <label class="form-check-label text-white-50 small"
                                                for="autoScroll">Auto-scroll</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="prettyPrint" checked>
                                            <label class="form-check-label text-white-50 small" for="prettyPrint">Pretty
                                                JSON</label>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="clearMonitorLog()">
                                            <i class="bi bi-trash"></i> Clear
                                        </button>
                                    </div>
                                </div>
                                <div class="monitor-log" id="monitorLog"
                                    style="background: rgba(0,0,0,0.4); border-radius: 12px; padding: 1rem; height: 350px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                                    <div class="text-white-50 text-center py-5">
                                        <i class="bi bi-broadcast" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">Klik "Start" untuk mulai monitoring...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Random Data Tab -->
                        <div class="tab-pane fade" id="bulkTab">
                            <p class="text-white-50 mb-3">Kirim data sensor random secara bulk untuk testing performa
                            </p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Jumlah Data</label>
                                    <input type="number" class="form-control form-control-glass" id="bulkCount"
                                        value="100" min="1" max="1000" placeholder="Jumlah record">
                                    <small class="text-white-50">Max 1000 records</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Interval (ms)</label>
                                    <input type="number" class="form-control form-control-glass" id="bulkInterval"
                                        value="1000" min="100" max="10000" step="100" placeholder="Interval ms">
                                    <small class="text-white-50">100ms - 10000ms</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Variasi Data</label>
                                    <select class="form-select form-select-glass" id="bulkVariation">
                                        <option value="random">Full Random</option>
                                        <option value="increment">Increment (+1)</option>
                                        <option value="wave">Wave (Sin)</option>
                                        <option value="spike">Random Spike</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Sensor Range Settings -->
                            <div class="sensor-field mb-3" id="bulkSensorRanges">
                                <h6 class="text-white mb-3"><i class="bi bi-sliders me-2"></i>Range Nilai Sensor</h6>
                                <div class="text-white-50 text-center py-3">
                                    <i class="bi bi-arrow-left-circle me-2"></i>
                                    Pilih device untuk melihat sensor ranges
                                </div>
                            </div>

                            <!-- Progress Section -->
                            <div class="sensor-field mb-3" id="bulkProgressSection" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-white">Progress</span>
                                    <span class="badge bg-info" id="bulkProgressText">0 / 0</span>
                                </div>
                                <div class="progress" style="height: 10px; background: rgba(0,0,0,0.3);">
                                    <div class="progress-bar bg-success" id="bulkProgressBar" role="progressbar"
                                        style="width: 0%"></div>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <small class="text-white-50" id="bulkStatus">Menunggu...</small>
                                    <small class="text-warning" id="bulkSpeed">0 msg/s</small>
                                </div>
                            </div>

                            <!-- Bulk Stats -->
                            <div class="row g-3 mb-3" id="bulkStats" style="display: none;">
                                <div class="col-md-3">
                                    <div class="sensor-field text-center">
                                        <div class="text-white-50 small">Terkirim</div>
                                        <div class="text-success fs-4" id="statSent">0</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="sensor-field text-center">
                                        <div class="text-white-50 small">Gagal</div>
                                        <div class="text-danger fs-4" id="statFailed">0</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="sensor-field text-center">
                                        <div class="text-white-50 small">Avg Time</div>
                                        <div class="text-info fs-4" id="statAvgTime">0ms</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="sensor-field text-center">
                                        <div class="text-white-50 small">Total Time</div>
                                        <div class="text-warning fs-4" id="statTotalTime">0s</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Connection Status -->
                            <div class="sensor-field mb-3" id="bulkConnectionStatus">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-white"><i class="bi bi-plug me-2"></i>Status Koneksi MQTT</span>
                                    <span class="badge bg-secondary" id="bulkConnBadge">
                                        <i class="bi bi-circle-fill me-1"></i>Belum Terkoneksi
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-info" id="testConnectBtn" disabled
                                    onclick="testBulkConnection()">
                                    <i class="bi bi-plug me-1"></i> Test Connect
                                    <span class="loading-spinner spinner-border spinner-border-sm ms-1"
                                        id="connectSpinner" style="display:none;"></span>
                                </button>
                                <button class="btn btn-gradient" id="startBulkBtn" disabled onclick="startBulkSend()">
                                    <i class="bi bi-play-fill me-1"></i> Start Bulk Send
                                </button>
                                <button class="btn btn-outline-danger" id="stopBulkBtn" disabled
                                    onclick="stopBulkSend()">
                                    <i class="bi bi-stop-fill me-1"></i> Stop
                                </button>
                                <button class="btn btn-outline-light" onclick="resetBulkStats()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Result Box -->
                    <div class="mt-4">
                        <h6 class="text-white"><i class="bi bi-terminal me-2"></i>Result Log</h6>
                        <div class="result-box" id="resultBox">
                            <pre id="resultContent">Menunggu pengiriman data...</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let selectedDevice = null;
        let deviceSensors = [];
        let deviceOutputs = [];

        // Device selection handler
        document.getElementById('deviceSelect').addEventListener('change', async function () {
            const deviceId = this.value;
            if (!deviceId) {
                resetFields();
                return;
            }

            try {
                const response = await fetch(`/admin/mqtt-tester/device/${deviceId}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();

                if (data.success) {
                    selectedDevice = data.device;
                    deviceSensors = data.sensors;
                    deviceOutputs = data.outputs;

                    // Update token and topic fields
                    document.getElementById('manualToken').value = data.device.token;
                    document.getElementById('mqttTopic').value = data.device.mqtt_topic;

                    // Show device info
                    showDeviceInfo(data);

                    // Generate form fields
                    generateSensorFields(data.sensors);
                    generateOutputFields(data.outputs);
                    populateScheduleOutputs(data.outputs);
                    populateSensorDropdown(data.sensors);

                    // Generate bulk random sensor ranges
                    generateBulkSensorRanges(data.sensors);

                    // Enable buttons
                    enableButtons();
                }
            } catch (error) {
                console.error('Error fetching device:', error);
            }
        });

        // Manual token input
        document.getElementById('manualToken').addEventListener('input', function () {
            if (this.value && document.getElementById('mqttTopic').value) {
                enableButtons();
            }
        });

        document.getElementById('mqttTopic').addEventListener('input', function () {
            if (this.value && document.getElementById('manualToken').value) {
                enableButtons();
            }
        });

        // Schedule type toggle
        document.getElementById('scheduleType').addEventListener('change', function () {
            document.getElementById('timeScheduleFields').style.display = this.value === 'time' ? 'block' : 'none';
            document.getElementById('sensorScheduleFields').style.display = this.value === 'sensor' ? 'block' : 'none';
        });

        function showDeviceInfo(data) {
            const infoDiv = document.getElementById('deviceInfo');
            const contentDiv = document.getElementById('deviceInfoContent');

            contentDiv.innerHTML = `
                <div class="text-white small">
                    <div><strong>Nama:</strong> ${data.device.name}</div>
                    <div><strong>Token:</strong> <code>${data.device.token}</code></div>
                    <div><strong>Topic:</strong> <code>${data.device.mqtt_topic}</code></div>
                    <div><strong>Sensors:</strong> ${data.sensors.length}</div>
                    <div><strong>Outputs:</strong> ${data.outputs.length}</div>
                </div>
            `;
            infoDiv.style.display = 'block';
        }

        function generateSensorFields(sensors) {
            const container = document.getElementById('sensorFields');
            if (sensors.length === 0) {
                container.innerHTML = '<div class="text-white-50 text-center py-4">Device ini tidak punya sensor</div>';
                return;
            }

            let html = '';
            sensors.forEach(sensor => {
                html += `
                    <div class="sensor-field">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge-sensor">${sensor.sensor_label}</span>
                            <small class="text-white-50">${sensor.sensor_name} (${sensor.unit || '-'})</small>
                        </div>
                        <input type="number" step="0.1" class="form-control form-control-glass sensor-input" 
                            data-sensor="${sensor.sensor_name}" placeholder="Masukkan nilai..."
                            value="${(Math.random() * 50 + 20).toFixed(1)}">
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function generateOutputFields(outputs) {
            const container = document.getElementById('outputFields');
            if (outputs.length === 0) {
                container.innerHTML = '<div class="text-white-50 text-center py-4">Device ini tidak punya output</div>';
                return;
            }

            let html = '';
            outputs.forEach(output => {
                if (output.output_type === 'boolean') {
                    html += `
                        <div class="output-field">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge-output">${output.output_label}</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input output-input" type="checkbox" 
                                        data-output="${output.output_name}" data-type="boolean" id="out_${output.output_name}">
                                    <label class="form-check-label text-white" for="out_${output.output_name}">ON</label>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="output-field">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge-output">${output.output_label}</span>
                                <small class="text-white-50">${output.output_type} (${output.unit || '-'})</small>
                            </div>
                            <input type="range" class="form-range output-input" 
                                data-output="${output.output_name}" data-type="${output.output_type}"
                                min="0" max="${output.output_type === 'percentage' ? 100 : 180}" value="50">
                            <div class="text-center text-warning" id="range_${output.output_name}">50</div>
                        </div>
                    `;
                }
            });
            container.innerHTML = html;

            // Add range value listeners
            document.querySelectorAll('.output-input[type="range"]').forEach(input => {
                input.addEventListener('input', function () {
                    document.getElementById('range_' + this.dataset.output).textContent = this.value;
                });
            });
        }

        function populateScheduleOutputs(outputs) {
            const select = document.getElementById('scheduleOutput');
            select.innerHTML = '<option value="">-- Pilih Output --</option>';
            outputs.forEach(output => {
                select.innerHTML += `<option value="${output.output_name}">${output.output_label}</option>`;
            });
        }

        function populateSensorDropdown(sensors) {
            const select = document.getElementById('ruleSensor');
            select.innerHTML = '';
            sensors.forEach(sensor => {
                select.innerHTML += `<option value="${sensor.sensor_name}">${sensor.sensor_label}</option>`;
            });
        }

        function enableButtons() {
            document.getElementById('sendSensorBtn').disabled = false;
            document.getElementById('sendOutputBtn').disabled = false;
            document.getElementById('sendScheduleBtn').disabled = false;
            document.getElementById('sendStatusBtn').disabled = false;
            document.getElementById('randomizeBtn').disabled = false;
        }

        // Generate random values for all sensor inputs
        function randomizeSensorValues() {
            const sensorInputs = document.querySelectorAll('.sensor-input');
            sensorInputs.forEach(input => {
                const sensorName = input.dataset.sensor;
                let min = 0, max = 100;

                // Set realistic ranges based on sensor type
                if (sensorName.includes('temp')) { min = 15; max = 45; }
                else if (sensorName.includes('hum') || sensorName.includes('soil')) { min = 20; max = 95; }
                else if (sensorName.includes('light') || sensorName.includes('lux')) { min = 100; max = 10000; }
                else if (sensorName.includes('rain')) { min = 0; max = 200; }
                else if (sensorName.includes('wind')) { min = 0; max = 50; }
                else if (sensorName.includes('pres')) { min = 990; max = 1030; }
                else if (sensorName.includes('ph')) { min = 4; max = 9; }
                else if (sensorName.includes('ec') || sensorName.includes('tds')) { min = 200; max = 2000; }

                const value = (Math.random() * (max - min) + min).toFixed(1);
                input.value = value;
            });
            showResult(true, { message: 'Random values generated!', timestamp: new Date().toISOString() });
        }

        function resetFields() {
            document.getElementById('sensorFields').innerHTML = '<div class="text-white-50 text-center py-4">Pilih device untuk melihat sensor fields</div>';
            document.getElementById('outputFields').innerHTML = '<div class="text-white-50 text-center py-4">Pilih device untuk melihat output fields</div>';
            document.getElementById('deviceInfo').style.display = 'none';
            document.getElementById('sendSensorBtn').disabled = true;
            document.getElementById('sendOutputBtn').disabled = true;
            document.getElementById('sendScheduleBtn').disabled = true;
            document.getElementById('sendStatusBtn').disabled = true;
        }

        function showResult(success, data) {
            const box = document.getElementById('resultBox');
            const content = document.getElementById('resultContent');

            box.classList.remove('result-success', 'result-error');
            box.classList.add(success ? 'result-success' : 'result-error');

            const timestamp = new Date().toLocaleTimeString();
            content.textContent = `[${timestamp}] ${success ? '✓ SUCCESS' : '✗ ERROR'}\n\n${JSON.stringify(data, null, 2)}`;
        }

        function setLoading(btn, loading) {
            const spinner = btn.querySelector('.loading-spinner');
            if (loading) {
                btn.disabled = true;
                spinner.classList.add('show');
            } else {
                btn.disabled = false;
                spinner.classList.remove('show');
            }
        }

        // Send Sensor Data
        document.getElementById('sendSensorBtn').addEventListener('click', async function () {
            const sensorData = {};
            document.querySelectorAll('.sensor-input').forEach(input => {
                sensorData[input.dataset.sensor] = parseFloat(input.value);
            });

            setLoading(this, true);

            try {
                const response = await fetch('/admin/mqtt-tester/send-sensor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: document.getElementById('manualToken').value,
                        mqtt_topic: document.getElementById('mqttTopic').value,
                        sensor_data: sensorData
                    })
                });
                const data = await response.json();
                showResult(data.success, data);
            } catch (error) {
                showResult(false, { error: error.message });
            }

            setLoading(this, false);
        });

        // Send Output Control
        document.getElementById('sendOutputBtn').addEventListener('click', async function () {
            const outputs = {};
            document.querySelectorAll('.output-input').forEach(input => {
                if (input.dataset.type === 'boolean') {
                    outputs[input.dataset.output] = input.checked;
                } else {
                    outputs[input.dataset.output] = parseInt(input.value);
                }
            });

            setLoading(this, true);

            try {
                const response = await fetch('/admin/mqtt-tester/send-output', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: document.getElementById('manualToken').value,
                        mqtt_topic: document.getElementById('mqttTopic').value,
                        outputs: outputs
                    })
                });
                const data = await response.json();
                showResult(data.success, data);
            } catch (error) {
                showResult(false, { error: error.message });
            }

            setLoading(this, false);
        });

        // Send Schedule
        document.getElementById('sendScheduleBtn').addEventListener('click', async function () {
            const scheduleType = document.getElementById('scheduleType').value;
            const outputName = document.getElementById('scheduleOutput').value;

            if (!outputName) {
                showResult(false, { error: 'Pilih output target terlebih dahulu!' });
                return;
            }

            let scheduleData;
            if (scheduleType === 'time') {
                scheduleData = [{
                    time_on: document.getElementById('timeOn').value,
                    time_off: document.getElementById('timeOff').value,
                    days: document.getElementById('scheduleDays').value.split(',').map(d => parseInt(d.trim()))
                }];
            } else {
                scheduleData = {
                    sensor: document.getElementById('ruleSensor').value,
                    operator: document.getElementById('ruleOperator').value,
                    threshold: parseFloat(document.getElementById('ruleThreshold').value),
                    action_true: parseInt(document.getElementById('actionTrue').value),
                    action_false: parseInt(document.getElementById('actionFalse').value)
                };
            }

            setLoading(this, true);

            try {
                const response = await fetch('/admin/mqtt-tester/send-schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: document.getElementById('manualToken').value,
                        mqtt_topic: document.getElementById('mqttTopic').value,
                        output_name: outputName,
                        schedule_type: scheduleType,
                        schedule_data: scheduleData
                    })
                });
                const data = await response.json();
                showResult(data.success, data);
            } catch (error) {
                showResult(false, { error: error.message });
            }

            setLoading(this, false);
        });

        // Request Status
        document.getElementById('sendStatusBtn').addEventListener('click', async function () {
            setLoading(this, true);

            try {
                const response = await fetch('/admin/mqtt-tester/request-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: document.getElementById('manualToken').value,
                        mqtt_topic: document.getElementById('mqttTopic').value
                    })
                });
                const data = await response.json();
                showResult(data.success, data);
            } catch (error) {
                showResult(false, { error: error.message });
            }

            setLoading(this, false);
        });

        // ========== CUSTOM JSON FUNCTIONS ==========

        function loadTemplate(type) {
            const token = document.getElementById('manualToken').value || 'YOUR_TOKEN_HERE';
            let template = {};

            if (type === 'sensor') {
                template = {
                    token: token,
                    temperature: 28.5,
                    humidity: 65.2,
                    soil_moisture: 45.0
                };
                document.getElementById('topicSuffix').value = '';
            } else if (type === 'output') {
                template = {
                    token: token,
                    action: 'set_output',
                    outputs: {
                        relay_1: true,
                        pump: false,
                        fan_speed: 75
                    },
                    timestamp: new Date().toISOString()
                };
                document.getElementById('topicSuffix').value = '/control';
            } else if (type === 'schedule') {
                template = {
                    type: 'time_schedule',
                    token: token,
                    output: 'relay_1',
                    schedules: [
                        {
                            time_on: '06:00',
                            time_off: '18:00',
                            days: [1, 2, 3, 4, 5]
                        }
                    ],
                    timestamp: new Date().toISOString()
                };
                document.getElementById('topicSuffix').value = '/control';
            }

            document.getElementById('customJson').value = JSON.stringify(template, null, 4);
        }

        function formatJson() {
            const textarea = document.getElementById('customJson');
            try {
                const json = JSON.parse(textarea.value);
                textarea.value = JSON.stringify(json, null, 4);
                showResult(true, { message: 'JSON formatted successfully!' });
            } catch (e) {
                showResult(false, { error: 'Invalid JSON: ' + e.message });
            }
        }

        function clearJson() {
            document.getElementById('customJson').value = '';
            document.getElementById('customTopic').value = '';
            document.getElementById('topicSuffix').value = '';
        }

        // Send Custom JSON
        document.getElementById('sendCustomBtn').addEventListener('click', async function () {
            const jsonText = document.getElementById('customJson').value.trim();

            if (!jsonText) {
                showResult(false, { error: 'JSON payload tidak boleh kosong!' });
                return;
            }

            // Validate JSON
            let payload;
            try {
                payload = JSON.parse(jsonText);
            } catch (e) {
                showResult(false, { error: 'Invalid JSON: ' + e.message });
                return;
            }

            // Determine topic
            let topic = document.getElementById('customTopic').value.trim();
            if (!topic) {
                topic = document.getElementById('mqttTopic').value;
            }

            if (!topic) {
                showResult(false, { error: 'Pilih device atau masukkan MQTT topic!' });
                return;
            }

            // Add suffix
            const suffix = document.getElementById('topicSuffix').value;
            topic = topic + suffix;

            setLoading(this, true);

            try {
                const response = await fetch('/admin/mqtt-tester/send-custom', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        mqtt_topic: topic,
                        payload: payload
                    })
                });
                const data = await response.json();
                showResult(data.success, data);
            } catch (error) {
                showResult(false, { error: error.message });
            }

            setLoading(this, false);
        });

        // ========== LIVE MONITOR FUNCTIONS ==========

        let mqttClient = null;
        let isSubscribed = false;
        let messageCounter = 0;

        function useDeviceTopic() {
            const topic = document.getElementById('mqttTopic').value;
            if (topic) {
                document.getElementById('subscribeTopic').value = topic + '/#';
            } else {
                showResult(false, { error: 'Pilih device terlebih dahulu!' });
            }
        }

        function addWildcard(wildcard) {
            const input = document.getElementById('subscribeTopic');
            let topic = input.value.trim();

            if (topic.endsWith('/')) {
                topic += wildcard;
            } else if (topic) {
                topic += '/' + wildcard;
            } else {
                topic = wildcard;
            }

            input.value = topic;
        }

        function addSuffix(suffix) {
            const input = document.getElementById('subscribeTopic');
            let topic = input.value.trim();

            // Remove existing wildcard at the end
            if (topic.endsWith('/#') || topic.endsWith('/+')) {
                topic = topic.slice(0, -2);
            }

            if (!topic.endsWith(suffix)) {
                input.value = topic + suffix;
            }
        }

        function toggleSubscription() {
            if (isSubscribed) {
                stopSubscription();
            } else {
                startSubscription();
            }
        }

        function startSubscription() {
            const broker = document.getElementById('wsBroker').value.trim();
            const topic = document.getElementById('subscribeTopic').value.trim();

            if (!topic) {
                showResult(false, { error: 'Masukkan topic untuk subscribe!' });
                return;
            }

            updateStatus('connecting');

            try {
                mqttClient = mqtt.connect(broker, {
                    clientId: 'webmonitor_' + Math.random().toString(16).substr(2, 8),
                    clean: true,
                    connectTimeout: 10000,
                    reconnectPeriod: 5000
                });

                mqttClient.on('connect', function () {
                    console.log('Connected to MQTT broker');
                    updateStatus('connected');

                    mqttClient.subscribe(topic, { qos: 1 }, function (err) {
                        if (err) {
                            addLogMessage('error', 'Subscribe Error', err.message);
                        } else {
                            addLogMessage('system', 'Subscribed', `Listening to: ${topic}`);
                            isSubscribed = true;
                            updateButton(true);
                        }
                    });
                });

                mqttClient.on('message', function (receivedTopic, message) {
                    messageCounter++;
                    updateMessageCount();

                    let payload = message.toString();
                    const prettyPrint = document.getElementById('prettyPrint').checked;

                    // Try to parse as JSON
                    try {
                        const json = JSON.parse(payload);
                        if (prettyPrint) {
                            payload = JSON.stringify(json, null, 2);
                        }
                        addLogMessage('data', receivedTopic, payload, true);
                    } catch (e) {
                        addLogMessage('data', receivedTopic, payload, false);
                    }
                });

                mqttClient.on('error', function (error) {
                    console.error('MQTT Error:', error);
                    updateStatus('error');
                    addLogMessage('error', 'Connection Error', error.message);
                });

                mqttClient.on('close', function () {
                    console.log('MQTT Connection closed');
                    if (isSubscribed) {
                        updateStatus('disconnected');
                        addLogMessage('system', 'Disconnected', 'Connection closed');
                    }
                });

                mqttClient.on('reconnect', function () {
                    updateStatus('connecting');
                    addLogMessage('system', 'Reconnecting', 'Attempting to reconnect...');
                });

            } catch (error) {
                updateStatus('error');
                addLogMessage('error', 'Error', error.message);
            }
        }

        function stopSubscription() {
            if (mqttClient) {
                mqttClient.end(true);
                mqttClient = null;
            }
            isSubscribed = false;
            updateStatus('disconnected');
            updateButton(false);
            addLogMessage('system', 'Stopped', 'Subscription stopped');
        }

        function updateStatus(status) {
            const statusEl = document.getElementById('connectionStatus');
            const statusMap = {
                'connecting': { class: 'bg-warning', text: 'Connecting...' },
                'connected': { class: 'bg-success', text: 'Connected' },
                'disconnected': { class: 'bg-secondary', text: 'Disconnected' },
                'error': { class: 'bg-danger', text: 'Error' }
            };

            const s = statusMap[status] || statusMap['disconnected'];
            statusEl.className = `badge ${s.class}`;
            statusEl.innerHTML = `<i class="bi bi-circle-fill me-1"></i> ${s.text}`;
        }

        function updateButton(subscribed) {
            const btn = document.getElementById('subscribeBtn');
            if (subscribed) {
                btn.innerHTML = '<i class="bi bi-stop-fill me-1"></i> Stop';
                btn.classList.remove('btn-gradient');
                btn.classList.add('btn-danger');
            } else {
                btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Start';
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-gradient');
            }
        }

        function updateMessageCount() {
            document.getElementById('messageCount').textContent = `${messageCounter} messages`;
        }

        function addLogMessage(type, title, content, isJson = false) {
            const log = document.getElementById('monitorLog');
            const timestamp = new Date().toLocaleTimeString();

            // Remove placeholder if first message
            if (log.querySelector('.text-center')) {
                log.innerHTML = '';
            }

            const colors = {
                'system': '#7dd3fc',
                'data': '#86efac',
                'error': '#fca5a5'
            };

            const entry = document.createElement('div');
            entry.style.marginBottom = '0.75rem';
            entry.style.paddingBottom = '0.75rem';
            entry.style.borderBottom = '1px solid rgba(255,255,255,0.1)';

            let contentHtml = '';
            if (isJson) {
                contentHtml = `<pre style="color: #fde047; margin: 0.25rem 0 0 0; white-space: pre-wrap;">${escapeHtml(content)}</pre>`;
            } else {
                contentHtml = `<div style="color: #fff; margin-top: 0.25rem;">${escapeHtml(content)}</div>`;
            }

            entry.innerHTML = `
                <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">${timestamp}</div>
                <div style="color: ${colors[type] || '#fff'}; font-weight: 600;">${escapeHtml(title)}</div>
                ${contentHtml}
            `;

            log.appendChild(entry);

            // Auto scroll
            if (document.getElementById('autoScroll').checked) {
                log.scrollTop = log.scrollHeight;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function clearMonitorLog() {
            const log = document.getElementById('monitorLog');
            log.innerHTML = `
                <div class="text-white-50 text-center py-5">
                    <i class="bi bi-broadcast" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">Log cleared. ${isSubscribed ? 'Still listening...' : 'Klik "Start" untuk mulai monitoring...'}</p>
                </div>
            `;
            messageCounter = 0;
            updateMessageCount();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function () {
            if (mqttClient) {
                mqttClient.end(true);
            }
        });

        // ===== BULK RANDOM DATA FUNCTIONS =====
        let bulkInterval = null;
        let bulkSent = 0;
        let bulkFailed = 0;
        let bulkTotal = 0;
        let bulkStartTime = null;
        let bulkTimes = [];
        let bulkSensorRanges = {};

        // Generate sensor ranges when device is selected
        function generateBulkSensorRanges(sensors) {
            const container = document.getElementById('bulkSensorRanges');
            if (!sensors || sensors.length === 0) {
                container.innerHTML = `
                    <h6 class="text-white mb-3"><i class="bi bi-sliders me-2"></i>Range Nilai Sensor</h6>
                    <div class="text-white-50 text-center py-3">Device ini tidak punya sensor</div>
                `;
                return;
            }

            let html = '<h6 class="text-white mb-3"><i class="bi bi-sliders me-2"></i>Range Nilai Sensor</h6><div class="row g-2">';
            sensors.forEach(sensor => {
                const defaultMin = 0;
                const defaultMax = sensor.sensor_name.includes('temp') ? 50 :
                    sensor.sensor_name.includes('hum') ? 100 :
                        sensor.sensor_name.includes('soil') ? 100 : 1000;
                html += `
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-sensor small">${sensor.sensor_label}</span>
                            <input type="number" class="form-control form-control-glass form-control-sm bulk-range-min" 
                                data-sensor="${sensor.sensor_name}" value="${defaultMin}" placeholder="Min" style="width: 70px;">
                            <span class="text-white-50">-</span>
                            <input type="number" class="form-control form-control-glass form-control-sm bulk-range-max" 
                                data-sensor="${sensor.sensor_name}" value="${defaultMax}" placeholder="Max" style="width: 70px;">
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;

            // Enable test connect button (start is enabled after successful connect)
            document.getElementById('testConnectBtn').disabled = false;
            document.getElementById('startBulkBtn').disabled = true;
        }

        // Test MQTT connection before bulk send
        let bulkConnected = false;
        async function testBulkConnection() {
            const token = document.getElementById('manualToken').value || selectedDevice?.token;
            const topic = document.getElementById('mqttTopic').value || selectedDevice?.mqtt_topic;

            if (!token || !topic) {
                showResult(false, { error: 'Pilih device atau masukkan token dan topic terlebih dahulu' });
                return;
            }

            // Show loading
            document.getElementById('connectSpinner').style.display = 'inline-block';
            document.getElementById('testConnectBtn').disabled = true;
            document.getElementById('bulkConnBadge').innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Connecting...';
            document.getElementById('bulkConnBadge').className = 'badge bg-warning';

            try {
                // Send a test sensor data request to verify connection
                const testData = {};
                deviceSensors.forEach(s => {
                    testData[s.sensor_name] = 0;
                });

                const response = await fetch('/admin/mqtt-tester/send-sensor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: token,
                        mqtt_topic: topic,
                        sensor_data: testData
                    })
                });

                const data = await response.json();

                if (data.success) {
                    bulkConnected = true;
                    document.getElementById('bulkConnBadge').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Terkoneksi!';
                    document.getElementById('bulkConnBadge').className = 'badge bg-success';
                    document.getElementById('startBulkBtn').disabled = false;
                    showResult(true, { message: 'Koneksi MQTT berhasil! Siap untuk bulk send.', topic: topic });
                } else {
                    bulkConnected = false;
                    document.getElementById('bulkConnBadge').innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Gagal';
                    document.getElementById('bulkConnBadge').className = 'badge bg-danger';
                    document.getElementById('startBulkBtn').disabled = true;
                    showResult(false, { error: data.message || 'Koneksi gagal' });
                }
            } catch (error) {
                bulkConnected = false;
                document.getElementById('bulkConnBadge').innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Error';
                document.getElementById('bulkConnBadge').className = 'badge bg-danger';
                document.getElementById('startBulkBtn').disabled = true;
                showResult(false, { error: error.message });
            } finally {
                document.getElementById('connectSpinner').style.display = 'none';
                document.getElementById('testConnectBtn').disabled = false;
            }
        }

        // Generate random value based on variation type
        function generateValue(min, max, variation, index, total) {
            const range = max - min;
            switch (variation) {
                case 'random':
                    return (Math.random() * range + min).toFixed(2);
                case 'increment':
                    return (min + (range * index / total)).toFixed(2);
                case 'wave':
                    const phase = (index / total) * Math.PI * 4; // 2 full waves
                    return ((Math.sin(phase) + 1) / 2 * range + min).toFixed(2);
                case 'spike':
                    if (Math.random() < 0.1) { // 10% chance spike
                        return (max + range * 0.5).toFixed(2); // Spike above max
                    }
                    return (Math.random() * range + min).toFixed(2);
                default:
                    return (Math.random() * range + min).toFixed(2);
            }
        }

        // Start bulk sending
        async function startBulkSend() {
            const count = parseInt(document.getElementById('bulkCount').value) || 100;
            const interval = parseInt(document.getElementById('bulkInterval').value) || 1000;
            const variation = document.getElementById('bulkVariation').value;
            const token = document.getElementById('manualToken').value || selectedDevice?.token;
            const topic = document.getElementById('mqttTopic').value || selectedDevice?.mqtt_topic;

            if (!token || !topic) {
                showResult(false, { error: 'Pilih device atau masukkan token dan topic terlebih dahulu' });
                return;
            }

            // Get sensor ranges
            bulkSensorRanges = {};
            document.querySelectorAll('.bulk-range-min').forEach(input => {
                const sensor = input.dataset.sensor;
                const min = parseFloat(input.value) || 0;
                const max = parseFloat(document.querySelector(`.bulk-range-max[data-sensor="${sensor}"]`).value) || 100;
                bulkSensorRanges[sensor] = { min, max };
            });

            if (Object.keys(bulkSensorRanges).length === 0 && deviceSensors.length > 0) {
                deviceSensors.forEach(s => {
                    bulkSensorRanges[s.sensor_name] = { min: 0, max: 100 };
                });
            }

            // Reset stats
            bulkSent = 0;
            bulkFailed = 0;
            bulkTotal = count;
            bulkStartTime = Date.now();
            bulkTimes = [];

            // Update UI
            document.getElementById('bulkProgressSection').style.display = 'block';
            document.getElementById('bulkStats').style.display = 'flex';
            document.getElementById('startBulkBtn').disabled = true;
            document.getElementById('stopBulkBtn').disabled = false;
            updateBulkProgress();

            // Start sending
            let index = 0;
            bulkInterval = setInterval(async () => {
                if (index >= count) {
                    stopBulkSend();
                    return;
                }

                const sendStart = Date.now();

                // Generate sensor data
                const sensorData = {};
                Object.keys(bulkSensorRanges).forEach(sensor => {
                    const { min, max } = bulkSensorRanges[sensor];
                    sensorData[sensor] = parseFloat(generateValue(min, max, variation, index, count));
                });

                try {
                    const response = await fetch('/admin/mqtt-tester/send-sensor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            token: token,
                            mqtt_topic: topic,
                            sensor_data: sensorData
                        })
                    });

                    const data = await response.json();
                    const sendTime = Date.now() - sendStart;
                    bulkTimes.push(sendTime);

                    if (data.success) {
                        bulkSent++;
                    } else {
                        bulkFailed++;
                    }
                } catch (error) {
                    bulkFailed++;
                }

                index++;
                updateBulkProgress();
            }, interval);
        }

        // Stop bulk sending
        function stopBulkSend() {
            if (bulkInterval) {
                clearInterval(bulkInterval);
                bulkInterval = null;
            }
            document.getElementById('startBulkBtn').disabled = false;
            document.getElementById('stopBulkBtn').disabled = true;
            document.getElementById('bulkStatus').textContent = 'Selesai!';

            // Calculate final stats
            const totalTime = (Date.now() - bulkStartTime) / 1000;
            document.getElementById('statTotalTime').textContent = totalTime.toFixed(1) + 's';
        }

        // Update progress display
        function updateBulkProgress() {
            const progress = bulkTotal > 0 ? ((bulkSent + bulkFailed) / bulkTotal * 100) : 0;
            document.getElementById('bulkProgressBar').style.width = progress + '%';
            document.getElementById('bulkProgressText').textContent = `${bulkSent + bulkFailed} / ${bulkTotal}`;
            document.getElementById('bulkStatus').textContent = `Mengirim... (${bulkSent} sukses, ${bulkFailed} gagal)`;

            // Stats
            document.getElementById('statSent').textContent = bulkSent;
            document.getElementById('statFailed').textContent = bulkFailed;

            if (bulkTimes.length > 0) {
                const avgTime = bulkTimes.reduce((a, b) => a + b, 0) / bulkTimes.length;
                document.getElementById('statAvgTime').textContent = Math.round(avgTime) + 'ms';
            }

            const elapsed = (Date.now() - bulkStartTime) / 1000;
            if (elapsed > 0) {
                const speed = ((bulkSent + bulkFailed) / elapsed).toFixed(1);
                document.getElementById('bulkSpeed').textContent = speed + ' msg/s';
            }

            document.getElementById('statTotalTime').textContent = elapsed.toFixed(1) + 's';
        }

        // Reset stats
        function resetBulkStats() {
            stopBulkSend();
            bulkSent = 0;
            bulkFailed = 0;
            bulkTotal = 0;
            bulkTimes = [];

            document.getElementById('bulkProgressBar').style.width = '0%';
            document.getElementById('bulkProgressText').textContent = '0 / 0';
            document.getElementById('bulkStatus').textContent = 'Menunggu...';
            document.getElementById('bulkSpeed').textContent = '0 msg/s';
            document.getElementById('statSent').textContent = '0';
            document.getElementById('statFailed').textContent = '0';
            document.getElementById('statAvgTime').textContent = '0ms';
            document.getElementById('statTotalTime').textContent = '0s';
            document.getElementById('bulkProgressSection').style.display = 'none';
            document.getElementById('bulkStats').style.display = 'none';
        }

        // Update device selection to also generate bulk ranges
        const originalDeviceSelectHandler = document.getElementById('deviceSelect').onchange;
        document.getElementById('deviceSelect').addEventListener('change', function () {
            if (deviceSensors && deviceSensors.length > 0) {
                setTimeout(() => generateBulkSensorRanges(deviceSensors), 100);
            }
        });
    </script>
</body>

</html>