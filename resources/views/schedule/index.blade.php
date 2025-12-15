<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule - {{ $output->output_label }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #134e4a 0%, #166534 50%, #14532d 100%);
            min-height: 100vh;
            padding: 2rem 0;
            color: #fff;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            text-decoration: none;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3><i class="bi bi-calendar-check me-2"></i>{{ $output->output_label }} - Schedule</h3>
                    <p class="text-white-50 mb-0">Device: {{ $device->name }} | Mode:
                        {{ ucfirst($output->automation_mode) }}</p>
                </div>
                <a href="{{ route('monitoring.show', $userDevice->id) }}" class="btn-glass">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($output->automation_mode === 'time')
                <!-- TIME-BASED SCHEDULE -->
                <div class="glass-card">
                    <h5 class="mb-3"><i class="bi bi-clock me-2"></i>Time Schedule (Max: {{ $output->max_schedules }} slots)
                    </h5>
                    <form action="{{ route('schedule.time.store', [$userDevice->id, $output->id]) }}" method="POST">
                        @csrf
                        <div id="scheduleContainer">
                            @for($i = 0; $i < $output->max_schedules; $i++)
                                <div class="row g-3 mb-3 align-items-center">
                                    <div class="col-md-1"><strong>Slot {{ $i + 1 }}</strong></div>
                                    <div class="col-md-5">
                                        <label class="form-label small">ON Time</label>
                                        <input type="time" name="schedules[{{ $i }}][on_time]" class="form-control" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small">OFF Time</label>
                                        <input type="time" name="schedules[{{ $i }}][off_time]" class="form-control" required>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-send me-1"></i> Kirim ke Device
                        </button>
                    </form>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Schedule akan dikirim ke device via MQTT. Device akan
                        eksekusi otomatis setiap hari.
                    </div>
                </div>

            @elseif($output->automation_mode === 'sensor')
                <!-- SENSOR-BASED AUTOMATION -->
                <div class="glass-card">
                    <h5 class="mb-3"><i class="bi bi-speedometer me-2"></i>Sensor Automation</h5>
                    <p class="text-white-50">Sensor: <strong>{{ $output->automationSensor->sensor_label }}</strong></p>

                    <form action="{{ route('schedule.sensor.store', [$userDevice->id, $output->id]) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Operator</label>
                                <select name="operator" class="form-select" required>
                                    <option value=">">Greater Than (>)</option>
                                    <option value="<">Less Than (<)< /option>
                                    <option value=">=">Greater or Equal (>=)</option>
                                    <option value="<=">Less or Equal (<=)< /option>
                                    <option value="==">Equal (==)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Threshold</label>
                                <input type="number" name="threshold" class="form-control" step="0.01" placeholder="30.5"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hysteresis</label>
                                <input type="number" name="hysteresis" class="form-control" step="0.01" value="2"
                                    placeholder="2">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Value ON (saat kondisi TRUE)</label>
                                <input type="number" name="action_on" class="form-control" step="0.01" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Value OFF (saat kondisi FALSE)</label>
                                <input type="number" name="action_off" class="form-control" step="0.01" value="0" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-send me-1"></i> Kirim ke Device
                        </button>
                    </form>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Rule akan dikirim ke device. Device akan monitor sensor dan
                        control output otomatis.
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>