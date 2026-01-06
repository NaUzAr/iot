<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $userDevice->custom_name }} - Monitoring</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-green: #22c55e;
            --dark-green: #166534;
            --light-green: #86efac;
            --sky-blue: #0ea5e9;
            --light-sky: #7dd3fc;
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

        .page-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
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

        .device-type-badge {
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
            color: #fff;
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .sensor-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .sensor-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-green);
        }

        .sensor-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.3rem;
        }

        .sensor-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .sensor-value {
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .sensor-unit {
            color: #86efac;
            font-size: 1rem;
            font-weight: 600;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .card-title {
            color: #fff;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .last-update {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.85rem;
        }

        .no-data {
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
            padding: 3rem;
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

        .live-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        /* Table Styles */
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

        /* Pagination */
        .pagination-glass .page-link {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: #fff;
        }

        .pagination-glass .page-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .pagination-glass .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
        }

        .pagination-glass .page-item.disabled .page-link {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.3);
        }

        /* Tabs */
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

        /* Output Control Styles */
        .output-card {
            background: rgba(250, 204, 21, 0.1);
            border: 1px solid rgba(250, 204, 21, 0.3);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .output-card:hover {
            border-color: #fde047;
            transform: translateY(-3px);
        }

        .output-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.2rem;
        }

        .output-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 32px;
            margin: 0 auto;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
            border-radius: 32px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        .toggle-switch input:checked+.toggle-slider {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .toggle-switch input:checked+.toggle-slider:before {
            transform: translateX(28px);
        }

        .output-status {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        .output-status.on {
            color: #22c55e;
        }

        .output-status.off {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Range Slider */
        .range-slider {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            -webkit-appearance: none;
        }

        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            cursor: pointer;
        }

        .range-value {
            color: #fde047;
            font-size: 1.25rem;
            font-weight: 700;
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
                @if($isAdminView ?? false)
                    <a class="nav-link" href="{{ route('admin.devices.index') }}">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Device Manager
                    </a>
                @else
                    <a class="nav-link" href="{{ route('monitoring.index') }}">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Monitoring
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Header -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="device-title">
                    <i class="bi {{ $device->type === 'aws' ? 'bi-cloud-sun' : 'bi-flower1' }} me-2"></i>
                    @if($isAdminView ?? false)
                        {{ $device->name }}
                    @else
                        {{ $userDevice->custom_name }}
                    @endif
                </h1>
                <p class="text-white-50 mb-0 mt-1">
                    <span class="live-dot me-2"></span>
                    @if($latestData)
                        Terakhir update: {{ \Carbon\Carbon::parse($latestData->recorded_at)->diffForHumans() }}
                    @else
                        Menunggu data...
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if($isAdminView ?? false)
                    <span class="device-type-badge" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="bi bi-shield-check me-1"></i> Admin View
                    </span>
                @endif
                <span class="device-type-badge">
                    {{ strtoupper($device->type ?? 'DEVICE') }}
                </span>
                @if(!($isAdminView ?? false))
                    <button type="button" class="btn-glass" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-download me-1"></i> Download CSV
                    </button>
                @endif
            </div>
        </div>

        @if($latestData)
            <!-- Sensor Cards -->
            <div class="row g-4">
                @foreach($sensors as $sensor)
                    @php
                        $value = $latestData->{$sensor->sensor_name} ?? null;
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="sensor-card">
                            <div class="sensor-icon">
                                <i class="bi bi-thermometer-half text-white"></i>
                            </div>
                            <div class="sensor-label">{{ $sensor->sensor_label }}</div>
                            <div class="sensor-value">
                                @if($value !== null)
                                    {{ number_format($value, 1) }}
                                @else
                                    --
                                @endif
                            </div>
                            <div class="sensor-unit">{{ $sensor->unit }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($outputs->count() > 0 && !($isAdminView ?? false))
            <!-- Output Control Panel -->
            <div class="glass-card"
                style="background: rgba(250, 204, 21, 0.05); border-color: rgba(250, 204, 21, 0.2); margin-top: 1.5rem;">
                <h5 class="card-title" style="color: #fde047;">
                    <i class="bi bi-sliders me-2"></i>Kontrol Output
                </h5>
                <div class="row g-3 mt-2">
                    @foreach($outputs as $output)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="output-card" id="output-card-{{ $output->id }}">
                                <div class="output-icon">
                                    <i class="bi bi-toggle-on text-white"></i>
                                </div>
                                <div class="output-label">{{ $output->output_label }}</div>

                                @if($output->output_type === 'boolean')
                                    {{-- ON/OFF Buttons for Boolean --}}
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button"
                                            class="btn btn-sm {{ $output->current_value ? 'btn-success' : 'btn-outline-success' }}"
                                            onclick="setOutput({{ $output->id }}, true)" id="btn-on-{{ $output->id }}"
                                            style="min-width: 50px;">
                                            <i class="bi bi-power"></i> ON
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm {{ !$output->current_value ? 'btn-danger' : 'btn-outline-danger' }}"
                                            onclick="setOutput({{ $output->id }}, false)" id="btn-off-{{ $output->id }}"
                                            style="min-width: 50px;">
                                            <i class="bi bi-x-lg"></i> OFF
                                        </button>
                                    </div>
                                    <div class="output-status {{ $output->current_value ? 'on' : 'off' }}"
                                        id="output-status-{{ $output->id }}">
                                        {{ $output->current_value ? 'ON' : 'OFF' }}
                                    </div>
                                @else
                                    <!-- Range Slider for Number/Percentage -->
                                    <div class="range-value" id="output-value-{{ $output->id }}">
                                        {{ (int) $output->current_value }}{{ $output->unit }}
                                    </div>
                                    <input type="range" class="range-slider mt-2" id="output-{{ $output->id }}"
                                        data-output-id="{{ $output->id }}" data-output-type="{{ $output->output_type }}" min="0"
                                        max="{{ $output->output_type === 'percentage' ? 100 : 180 }}"
                                        value="{{ (int) $output->current_value }}"
                                        oninput="updateRangeValue({{ $output->id }}, this.value, '{{ $output->unit }}')"
                                        onchange="toggleOutput({{ $output->id }}, this.value)">
                                    <div class="output-status on mt-1">
                                        {{ $output->output_type === 'percentage' ? '0-100%' : '0-180°' }}
                                    </div>
                                @endif

                                {{-- Always reserve space for button to maintain consistent card height --}}
                                <div class="mt-2">
                                    @if($output->automation_mode !== 'none')
                                        <a href="{{ route('schedule.index', [$userDevice->id, $output->id]) }}"
                                            class="btn btn-sm btn-outline-light w-100" style="font-size: 0.75rem;">
                                            <i class="bi bi-calendar-check"></i> Schedule
                                        </a>
                                    @else
                                        <div style="height: 31px;"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($latestData)
            <!-- Tabs -->
            @php
                $isTableActive = request()->has('page');
            @endphp
            <div id="data-section"></div>
            <ul class="nav nav-tabs nav-tabs-glass mt-4" id="dataTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ !$isTableActive ? 'active' : '' }}" data-bs-toggle="tab"
                        data-bs-target="#chartTab">
                        <i class="bi bi-graph-up me-1"></i> Grafik
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $isTableActive ? 'active' : '' }}" data-bs-toggle="tab"
                        data-bs-target="#tableTab">
                        <i class="bi bi-table me-1"></i> Tabel Data ({{ $logData->total() }} records)
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Chart Tab -->
                <div class="tab-pane fade {{ !$isTableActive ? 'show active' : '' }}" id="chartTab">
                    <div class="glass-card mt-0" style="border-radius: 0 0 20px 20px;">
                        <canvas id="sensorChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Table Tab -->
                <div class="tab-pane fade {{ $isTableActive ? 'show active' : '' }}" id="tableTab">
                    <div class="glass-card mt-0" style="border-radius: 0 0 20px 20px;">
                        <div class="table-responsive">
                            <table class="table table-glass mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Waktu</th>
                                        @foreach($sensors as $sensor)
                                            <th>{{ $sensor->sensor_label }} ({{ $sensor->unit }})</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logData as $index => $row)
                                        <tr>
                                            <td>{{ $logData->firstItem() + $index }}</td>
                                            <td>{{ \Carbon\Carbon::parse($row->recorded_at)->format('d/m/Y H:i:s') }}</td>
                                            @foreach($sensors as $sensor)
                                                <td>
                                                    @if(isset($row->{$sensor->sensor_name}))
                                                        {{ number_format($row->{$sensor->sensor_name}, 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($logData->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                <nav>
                                    <ul class="pagination pagination-glass mb-0">
                                        {{-- Previous --}}
                                        @if($logData->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">«</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link"
                                                    href="{{ $logData->previousPageUrl() }}#data-section">«</a></li>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @foreach($logData->getUrlRange(max(1, $logData->currentPage() - 2), min($logData->lastPage(), $logData->currentPage() + 2)) as $page => $url)
                                            @if($page == $logData->currentPage())
                                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $url }}#data-section">{{ $page }}</a></li>
                                            @endif
                                        @endforeach

                                        {{-- Next --}}
                                        @if($logData->hasMorePages())
                                            <li class="page-item"><a class="page-link"
                                                    href="{{ $logData->nextPageUrl() }}#data-section">»</a></li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link">»</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                            <p class="text-center text-white-50 mt-2 small">
                                Showing {{ $logData->firstItem() }} - {{ $logData->lastItem() }} of {{ $logData->total() }}
                                records
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <script>
                const ctx = document.getElementById('sensorChart').getContext('2d');

                // Data dari PHP - hanya 50 terbaru untuk chart
                const chartData = @json($chartData);
                const sensors = @json($sensors);

                const labels = chartData.map(row => {
                    const date = new Date(row.recorded_at);
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                });

                const colors = [
                    { border: '#22c55e', bg: 'rgba(34, 197, 94, 0.2)' },
                    { border: '#0ea5e9', bg: 'rgba(14, 165, 233, 0.2)' },
                    { border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.2)' },
                    { border: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.2)' },
                    { border: '#ef4444', bg: 'rgba(239, 68, 68, 0.2)' },
                    { border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { border: '#84cc16', bg: 'rgba(132, 204, 22, 0.2)' },
                    { border: '#ec4899', bg: 'rgba(236, 72, 153, 0.2)' },
                ];

                const datasets = sensors.map((sensor, index) => {
                    const colorIndex = index % colors.length;
                    return {
                        label: sensor.sensor_label + (sensor.unit ? ` (${sensor.unit})` : ''),
                        data: chartData.map(row => row[sensor.sensor_name]),
                        borderColor: colors[colorIndex].border,
                        backgroundColor: colors[colorIndex].bg,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: false,
                    };
                });

                new Chart(ctx, {
                    type: 'line',
                    data: { labels, datasets },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: { color: 'rgba(255,255,255,0.8)' }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: 'rgba(255,255,255,0.6)' },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            },
                            y: {
                                ticks: { color: 'rgba(255,255,255,0.6)' },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            }
                        }
                    }
                });
            </script>
        @else
            <!-- No Data -->
            <div class="glass-card">
                <div class="no-data">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-white">Belum Ada Data</h5>
                    <p>Device ini belum mengirimkan data sensor.<br>Data akan muncul setelah device terhubung dan mengirim
                        data.</p>
                </div>
            </div>

            <div class="glass-card mt-4">
                <h5 class="card-title"><i class="bi bi-list-check me-2"></i>Sensor yang Dikonfigurasi</h5>
                <div class="row g-3 mt-2">
                    @foreach($sensors as $sensor)
                        <div class="col-md-4">
                            <div class="d-flex align-items-center p-3"
                                style="background: rgba(255,255,255,0.05); border-radius: 12px;">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <div>
                                    <div class="text-white fw-semibold">{{ $sensor->sensor_label }}</div>
                                    <small class="text-white-50">{{ $sensor->sensor_name }}
                                        {{ $sensor->unit ? '(' . $sensor->unit . ')' : '' }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if(!($isAdminView ?? false))
    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: linear-gradient(135deg, #134e4a 0%, #166534 100%); border: 1px solid rgba(255,255,255,0.2);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="exportModalLabel">
                        <i class="bi bi-download me-2"></i>Download Data CSV
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('monitoring.export', $userDevice->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-white-50 mb-4">Pilih rentang tanggal untuk data yang ingin di-download:</p>

                        <div class="mb-3">
                            <label class="form-label text-white">
                                <i class="bi bi-calendar-event me-1"></i> Tanggal Mulai
                            </label>
                            <input type="date" name="start_date"
                                class="form-control bg-dark text-white border-secondary"
                                value="{{ date('Y-m-d', strtotime('-7 days')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">
                                <i class="bi bi-calendar-check me-1"></i> Tanggal Akhir
                            </label>
                            <input type="date" name="end_date" class="form-control bg-dark text-white border-secondary"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="setDateRange(7)">7
                                Hari</button>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="setDateRange(30)">30
                                Hari</button>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="setDateRange(90)">3
                                Bulan</button>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn" style="background: var(--primary-gradient); color: #fff;">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Setup CSRF token for AJAX requests
        const csrfToken = '{{ csrf_token() }}';
        const userDeviceId = {{ $userDevice->id }};

        // Set output ON/OFF (for buttons)
        function setOutput(outputId, isOn) {
            const url = `/monitoring/device/${userDeviceId}/output/${outputId}/toggle`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ value: isOn })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button styles
                        const btnOn = document.getElementById(`btn-on-${outputId}`);
                        const btnOff = document.getElementById(`btn-off-${outputId}`);
                        const statusEl = document.getElementById(`output-status-${outputId}`);

                        if (isOn) {
                            btnOn.className = 'btn btn-sm btn-success';
                            btnOff.className = 'btn btn-sm btn-outline-danger';
                        } else {
                            btnOn.className = 'btn btn-sm btn-outline-success';
                            btnOff.className = 'btn btn-sm btn-danger';
                        }

                        if (statusEl) {
                            statusEl.textContent = isOn ? 'ON' : 'OFF';
                            statusEl.className = isOn ? 'output-status on' : 'output-status off';
                        }

                        // Show success feedback
                        const card = document.getElementById(`output-card-${outputId}`);
                        if (card) {
                            card.style.borderColor = '#22c55e';
                            setTimeout(() => {
                                card.style.borderColor = 'rgba(250, 204, 21, 0.3)';
                            }, 500);
                        }

                        console.log('Output updated:', data.message);
                    } else {
                        console.error('Failed to update output');
                        alert('Gagal mengupdate output. Silakan coba lagi.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengupdate output.');
                });
        }

        // Toggle output (AJAX) - kept for range sliders
        function toggleOutput(outputId, value) {
            const url = `/monitoring/device/${userDeviceId}/output/${outputId}/toggle`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ value: value })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update status text for boolean
                        const statusEl = document.getElementById(`output-status-${outputId}`);
                        if (statusEl) {
                            const isOn = data.new_value == 1 || data.new_value === true;
                            statusEl.textContent = isOn ? 'ON' : 'OFF';
                            statusEl.className = isOn ? 'output-status on' : 'output-status off';
                        }

                        // Show success feedback
                        const card = document.getElementById(`output-card-${outputId}`);
                        if (card) {
                            card.style.borderColor = '#22c55e';
                            setTimeout(() => {
                                card.style.borderColor = 'rgba(250, 204, 21, 0.3)';
                            }, 500);
                        }

                        console.log('Output updated:', data.message);
                    } else {
                        console.error('Failed to update output');
                        alert('Gagal mengupdate output. Silakan coba lagi.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengupdate output.');
                });
        }

        // Update range value display
        function updateRangeValue(outputId, value, unit) {
            const valueEl = document.getElementById(`output-value-${outputId}`);
            if (valueEl) {
                valueEl.textContent = value + unit;
            }
        }

        function setDateRange(days) {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - days);

            document.querySelector('input[name="start_date"]').value = startDate.toISOString().split('T')[0];
            document.querySelector('input[name="end_date"]').value = endDate.toISOString().split('T')[0];
        }
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.pwa-scripts')
</body>

</html>