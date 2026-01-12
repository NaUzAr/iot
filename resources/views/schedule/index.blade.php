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

        .day-checkbox {
            display: inline-block;
            margin-right: 0.5rem;
        }

        .day-checkbox input[type="checkbox"] {
            display: none;
        }

        .day-checkbox label {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .day-checkbox input[type="checkbox"]:checked+label {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-color: #22c55e;
        }

        .schedule-slot {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #22c55e;
            color: #fff;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        }

        .form-select option {
            background: #166534;
            color: #fff;
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
                        {{ ucfirst(str_replace('_', ' + ', $output->automation_mode)) }}
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

            @if(in_array($output->automation_mode, ['time', 'time_days', 'time_days_sector']))
                <!-- TIME-BASED SCHEDULE -->
                <div class="glass-card">
                    <h5 class="mb-3">
                        <i class="bi bi-clock me-2"></i>Time Schedule (Max: {{ $output->max_schedules }} slots)
                        @if($output->automation_mode === 'time_days_sector')
                            | Sectors: {{ $output->max_sectors ?? 1 }}
                        @endif
                    </h5>
                    <div id="scheduleContainer">
                        @for($i = 0; $i < $output->max_schedules; $i++)
                            <div class="schedule-slot glass-card mb-3" id="slot-{{ $i }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-1">
                                        <strong class="text-white-50">Slot {{ $i + 1 }}</strong>
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <label class="form-label small">ON Time</label>
                                        <input type="time" id="on_time_{{ $i }}" class="form-control">
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <label class="form-label small">OFF Time</label>
                                        <input type="time" id="off_time_{{ $i }}" class="form-control">
                                    </div>

                                    @if(in_array($output->automation_mode, ['time_days', 'time_days_sector']))
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label small">Days</label>
                                            <div class="d-flex flex-wrap">
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_1" value="1" checked>
                                                    <label for="day_{{ $i }}_1">Sen</label>
                                                </div>
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_2" value="2" checked>
                                                    <label for="day_{{ $i }}_2">Sel</label>
                                                </div>
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_3" value="3" checked>
                                                    <label for="day_{{ $i }}_3">Rab</label>
                                                </div>
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_4" value="4" checked>
                                                    <label for="day_{{ $i }}_4">Kam</label>
                                                </div>
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_5" value="5" checked>
                                                    <label for="day_{{ $i }}_5">Jum</label>
                                                </div>
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_6" value="6">
                                                    <label for="day_{{ $i }}_6">Sab</label>
                                                </div>
                                                <div class="day-checkbox">
                                                    <input type="checkbox" id="day_{{ $i }}_7" value="7">
                                                    <label for="day_{{ $i }}_7">Min</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($output->automation_mode === 'time_days_sector')
                                        <div class="col-6 col-lg-1">
                                            <label class="form-label small">Sector</label>
                                            <select id="sector_{{ $i }}" class="form-select">
                                                @for($s = 1; $s <= ($output->max_sectors ?? 1); $s++)
                                                    <option value="{{ $s }}">{{ $s }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-6 col-lg-1">
                                        <button type="button" class="btn btn-primary w-100" onclick="sendSchedule({{ $i }})">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </div>
                                    <div class="col-12 col-lg-1">
                                        <span id="status_{{ $i }}" class="badge bg-secondary">Belum dikirim</span>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Klik tombol kirim pada setiap slot untuk mengirim jadwal ke
                        device via MQTT.
                        @if(in_array($output->automation_mode, ['time_days', 'time_days_sector']))
                            <br><small>Hari: 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu</small>
                        @endif
                    </div>
                </div>

                <script>
                    const automationMode = '{{ $output->automation_mode }}';

                    async function sendSchedule(slotIndex) {
                        const onTime = document.getElementById(`on_time_${slotIndex}`).value;
                        const offTime = document.getElementById(`off_time_${slotIndex}`).value;
                        const statusBadge = document.getElementById(`status_${slotIndex}`);

                        // Validation
                        if (!onTime || !offTime) {
                            alert('Mohon isi ON Time dan OFF Time terlebih dahulu!');
                            return;
                        }

                        // Get days if applicable
                        let days = '';
                        if (['time_days', 'time_days_sector'].includes(automationMode)) {
                            for (let d = 1; d <= 7; d++) {
                                const checkbox = document.getElementById(`day_${slotIndex}_${d}`);
                                if (checkbox && checkbox.checked) {
                                    days += d;
                                }
                            }
                            if (!days) {
                                alert('Pilih minimal satu hari!');
                                return;
                            }
                        }

                        // Get sector if applicable
                        let sector = null;
                        if (automationMode === 'time_days_sector') {
                            const sectorSelect = document.getElementById(`sector_${slotIndex}`);
                            sector = sectorSelect ? sectorSelect.value : 1;
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
                                    off_time: offTime,
                                    days: days || null,
                                    sector: sector
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

                    <form action="{{ route('schedule.sensor.store', [$userDevice->id, $output->id]) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Operator</label>
                                <select name="operator" class="form-select" required>
                                    <option value=">">Greater Than (>)</option>
                                    <option value="<">Less Than (<)< /option>
                                    <option value=">=">Greater or Equal (>=)</option>
                                    <option value="<=">Less or Equal (<=)< /option>
                                    <option value="==">Equal (==)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Threshold</label>
                                <input type="number" name="threshold" class="form-control" step="0.01" placeholder="30.5"
                                    required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-send me-1"></i> Kirim ke Device
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>