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
                        {{ ucfirst($output->automation_mode) }}
                    </p>
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
                    <div id="scheduleContainer">
                        @for($i = 0; $i < $output->max_schedules; $i++)
                            <div class="schedule-slot glass-card mb-3" id="slot-{{ $i }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-1">
                                        <strong class="text-white-50">Slot {{ $i + 1 }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">ON Time</label>
                                        <input type="time" id="on_time_{{ $i }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">OFF Time</label>
                                        <input type="time" id="off_time_{{ $i }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary w-100" onclick="sendSchedule({{ $i }})">
                                            <i class="bi bi-send me-1"></i> Kirim Slot {{ $i + 1 }}
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <span id="status_{{ $i }}" class="badge bg-secondary">Belum dikirim</span>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Klik tombol "Kirim" pada setiap slot untuk mengirim jadwal ke
                        device via MQTT.
                    </div>
                </div>

                <script>
                    async function sendSchedule(slotIndex) {
                        const onTime = document.getElementById(`on_time_${slotIndex}`).value;
                        const offTime = document.getElementById(`off_time_${slotIndex}`).value;
                        const statusBadge = document.getElementById(`status_${slotIndex}`);

                        // Validation
                        if (!onTime || !offTime) {
                            alert('Mohon isi ON Time dan OFF Time terlebih dahulu!');
                            return;
                        }

                        statusBadge.className = 'badge bg-warning';
                        statusBadge.textContent = 'Mengirim...';

                        try {
                            const response = await fetch('{{ route("schedule.time.store", [$userDevice->id, $output->id]) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    slot_id: slotIndex + 1,
                                    on_time: onTime,
                                    off_time: offTime
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                statusBadge.className = 'badge bg-success';
                                statusBadge.textContent = 'Terkirim ✓';
                            } else {
                                statusBadge.className = 'badge bg-danger';
                                statusBadge.textContent = 'Gagal';
                                alert(data.message || 'Gagal mengirim jadwal');
                            }
                        } catch (error) {
                            statusBadge.className = 'badge bg-danger';
                            statusBadge.textContent = 'Error';
                            alert('Error: ' + error.message);
                        }
                    }
                </script>

            @elseif($output->automation_mode === 'sensor')
                <!-- SENSOR-BASED AUTOMATION -->
                <div class="glass-card">
                    <h5 class="mb-3"><i class="bi bi-speedometer me-2"></i>Sensor Automation</h5>

                    @if($output->automationSensor)
                        <form action="{{ route('schedule.sensor.store', [$userDevice->id, $output->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="sensor" value="{{ $output->automationSensor->sensor_name }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Sensor</label>
                                    <input type="text" class="form-control"
                                        value="{{ $output->automationSensor->sensor_label }} ({{ $output->automationSensor->unit }})"
                                        readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Operator</label>
                                    <select name="operator" class="form-select" required>
                                        <option value=">">Greater Than (>)</option>
                                        <option value="<">Less Than (<)< /option>
                                        <option value=">=">Greater or Equal (>=)</option>
                                        <option value="<=">Less or Equal (<=)< /option>
                                        <option value="==">Equal (==)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Threshold</label>
                                    <input type="number" name="threshold" class="form-control" step="0.01" placeholder="30.5"
                                        required>
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
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i> Sensor belum dikonfigurasi.
                            Hapus device ini dan buat ulang dengan memilih sensor pada output mode "Sensor".
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>