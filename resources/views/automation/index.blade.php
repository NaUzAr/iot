<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Automation - {{ $device->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-green: #22c55e;
            --dark-green: #166534;
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

        .page-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--gl ass-border);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }

        .device-title {
            color: #fff;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }

        .btn-glass {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
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

        .table-glass {
            color: #fff;
        }

        .table-glass thead th {
            background: rgba(20, 83, 45, 0.8);
            color: #86efac;
            font-weight: 600;
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem;
        }

        .table-glass tbody td {
            border-bottom: 1px solid var(--glass-border);
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .table-glass tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .toggle-switch-small {
            position: relative;
            width: 50px;
            height: 26px;
            display: inline-block;
        }

        .toggle-switch-small input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider-small {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
            border-radius: 26px;
        }

        .toggle-slider-small:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        .toggle-switch-small input:checked+.toggle-slider-small {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .toggle-switch-small input:checked+.toggle-slider-small:before {
            transform: translateX(24px);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="device-title">
                    <i class="bi bi-robot me-2"></i> Automation - {{ $device->name }}
                </h1>
                <p class="text-white-50 mb-0 mt-1">Kelola automasi untuk output device</p>
            </div>
            <div class="d-flex gap-2">
                @if($device->canAddTimeSchedule() || $device->canAddSensorAutomation())
                    <a href="{{ route('automation.create', $device->id) }}" class="btn-glass">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Automation
                    </a>
                @endif
                <a href="{{ route('monitoring.show', $userDevice->id) }}" class="btn-glass">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success glass-card">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger glass-card">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Limits Info -->
        <div class="glass-card">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clock-history text-info me-3" style="font-size: 2rem;"></i>
                        <div>
                            <div class="text-white-50 small">Time-based Schedules</div>
                            <div class="text-white fw-bold">
                                {{ $timeAutomations->count() }} / {{ $device->max_time_schedules }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-speedometer text-warning me-3" style="font-size: 2rem;"></i>
                        <div>
                            <div class="text-white-50 small">Sensor-based Automations</div>
                            <div class="text-white fw-bold">
                                {{ $sensorAutomations->count() }} / {{ $device->max_sensor_automations }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs nav-tabs-glass">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#timeTab">
                    <i class="bi bi-clock me-1"></i> Time-based ({{ $timeAutomations->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sensorTab">
                    <i class="bi bi-speedometer me-1"></i> Sensor-based ({{ $sensorAutomations->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Time-based Tab -->
            <div class="tab-pane fade show active" id="timeTab">
                <div class="glass-card mt-0" style="border-radius: 0 0 20px 20px;">
                    @if($timeAutomations->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-glass">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Output</th>
                                        <th>Jadwal</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeAutomations as $auto)
                                        <tr>
                                            <td>{{ $auto->automation_name }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $auto->deviceOutput->output_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                                                    $days = collect($auto->schedule_days)->map(fn($d) => $dayNames[$d])->join(', ');
                                                @endphp
                                                {{ $days }}
                                            </td>
                                            <td>{{ $auto->schedule_time_start }} - {{ $auto->schedule_time_end }}</td>
                                            <td>
                                                <label class="toggle-switch-small">
                                                    <input type="checkbox" {{ $auto->enabled ? 'checked' : '' }}
                                                        onchange="toggleAutomation({{ $auto->id }}, this.checked)">
                                                    <span class="toggle-slider-small"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('automation.edit', $auto->id) }}"
                                                        class="btn btn-sm btn-outline-light">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('automation.destroy', $auto->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Yakin hapus automation ini?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-clock" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                            <p class="text-white-50 mt-3">Belum ada time-based automation</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sensor-based Tab -->
            <div class="tab-pane fade" id="sensorTab">
                <div class="glass-card mt-0" style="border-radius: 0 0 20px 20px;">
                    @if($sensorAutomations->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-glass">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Output</th>
                                        <th>Kondisi</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sensorAutomations as $auto)
                                        <tr>
                                            <td>{{ $auto->automation_name }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $auto->deviceOutput->output_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $conditions = collect($auto->sensor_conditions)->map(function ($cond) {
                                                        return "{$cond['sensor_name']} {$cond['operator']} {$cond['threshold']}";
                                                    })->join(" {$auto->condition_logic} ");
                                                @endphp
                                                <small>{{ $conditions }}</small>
                                            </td>
                                            <td>{{ $auto->action_value }}</td>
                                            <td>
                                                <label class="toggle-switch-small">
                                                    <input type="checkbox" {{ $auto->enabled ? 'checked' : '' }}
                                                        onchange="toggleAutomation({{ $auto->id }}, this.checked)">
                                                    <span class="toggle-slider-small"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('automation.edit', $auto->id) }}"
                                                        class="btn btn-sm btn-outline-light">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('automation.destroy', $auto->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Yakin hapus automation ini?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-speedometer" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                            <p class="text-white-50 mt-3">Belum ada sensor-based automation</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAutomation(id, enabled) {
            fetch(`/automation/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ enabled })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Gagal mengubah status automation');
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                    location.reload();
                });
        }
    </script>
</body>

</html>