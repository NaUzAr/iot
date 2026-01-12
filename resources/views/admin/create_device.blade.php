<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Device - Smart Agriculture</title>
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

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
        }

        .card-header-gradient {
            background: var(--primary-gradient);
            border-radius: 24px 24px 0 0 !important;
            padding: 1.5rem 2rem;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-green);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-select option {
            background: #166534;
            color: #fff;
        }

        .form-label {
            color: #86efac;
            font-weight: 600;
        }

        .form-text {
            color: rgba(255, 255, 255, 0.6);
        }

        .type-card {
            cursor: pointer;
            background: var(--glass-bg);
            border: 2px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            color: #fff;
        }

        .type-card:hover,
        .type-card.selected {
            border-color: var(--primary-green);
            background: rgba(34, 197, 94, 0.2);
            transform: translateY(-5px);
        }

        .type-card i {
            font-size: 2.5rem;
            color: var(--light-green);
        }

        .type-card h6 {
            margin-top: 0.5rem;
            margin-bottom: 0;
        }

        .sensor-row {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid var(--glass-border);
            transition: all 0.2s ease;
        }

        .sensor-row:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-green);
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            padding: 0.75rem 1.5rem;
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
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .btn-outline-add {
            background: transparent;
            border: 2px dashed var(--primary-green);
            color: var(--light-green);
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-add:hover {
            background: rgba(34, 197, 94, 0.2);
            color: #fff;
            border-style: solid;
        }

        .alert-info-custom {
            background: rgba(14, 165, 233, 0.2);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: var(--light-sky);
            border-radius: 12px;
        }

        .alert-warning-custom {
            background: rgba(250, 204, 21, 0.2);
            border: 1px solid rgba(250, 204, 21, 0.3);
            color: #fef08a;
            border-radius: 12px;
        }

        .badge-count {
            background: var(--secondary-gradient);
            color: #166534;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
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
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Devices
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card shadow-lg">
                    <div class="card-header-gradient">
                        <h4 class="mb-0 text-white">
                            <i class="bi bi-plus-circle-fill me-2"></i>Tambah Device Baru
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-white">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-white">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.device.store') }}" method="POST" id="deviceForm">
                            @csrf

                            <!-- STEP 1: TIPE ALAT -->
                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-cpu me-1"></i> Pilih Tipe Alat</label>
                                <div class="row g-3">
                                    @foreach($deviceTypes as $typeKey => $typeLabel)
                                        <div class="col-md-6">
                                            <div class="type-card" data-type="{{ $typeKey }}"
                                                onclick="selectDeviceType('{{ $typeKey }}')">
                                                <i
                                                    class="bi {{ $typeKey === 'aws' ? 'bi-cloud-sun-fill' : 'bi-flower1' }}"></i>
                                                <h6>{{ $typeLabel }}</h6>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="type" id="deviceType" value="" required>
                            </div>

                            <!-- STEP 2: INFO DEVICE -->
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-tag me-1"></i> Nama Device / Lokasi</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Contoh: Sensor Kebun Teh 01" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-broadcast me-1"></i> Alamat Topik MQTT</label>
                                <input type="text" name="mqtt_topic" class="form-control"
                                    placeholder="Contoh: sensor/kebun/data" required>
                                <div class="form-text">Device akan mengirim data ke topik ini.</div>
                            </div>

                            <!-- STEP 3: DAFTAR SENSOR -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-thermometer-half me-1"></i> Konfigurasi Sensor
                                    <span class="badge-count ms-2" id="sensorCount">0 sensor</span>
                                </label>
                                <div class="alert alert-info-custom py-2 mb-3">
                                    <small><i class="bi bi-info-circle me-1"></i>
                                        Tambahkan sensor sesuai kebutuhan. Bisa menambah sensor dengan jenis yang sama.
                                    </small>
                                </div>

                                <div id="sensorContainer"></div>

                                <button type="button" class="btn btn-outline-add w-100" onclick="addSensorRow()">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Sensor
                                </button>
                            </div>

                            <!-- STEP 4: DAFTAR OUTPUT -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-toggle-on me-1"></i> Konfigurasi Output (Opsional)
                                    <span class="badge-count ms-2" id="outputCount">0 output</span>
                                </label>
                                <div class="alert alert-info-custom py-2 mb-3">
                                    <small><i class="bi bi-info-circle me-1"></i>
                                        Output adalah aktuator yang dapat dikontrol via MQTT (relay, pompa, kipas, dll).
                                    </small>
                                </div>

                                <div id="outputContainer"></div>

                                <button type="button" class="btn btn-outline-add w-100" onclick="addOutputRow()">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Output
                                </button>
                            </div>

                            <div class="alert alert-warning-custom">
                                <strong><i class="bi bi-exclamation-triangle me-1"></i> Perhatian:</strong>
                                Sistem akan otomatis membuatkan <b>Token Unik</b> dan <b>Tabel Database Baru</b>.
                            </div>

                            <div class="d-flex gap-3 mt-4">
                                <a href="{{ route('admin.devices.index') }}" class="btn btn-glass">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-gradient flex-grow-1" id="submitBtn" disabled>
                                    <i class="bi bi-plus-circle me-1"></i> Generate Device & Tabel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const availableSensors = @json($availableSensors);
        const availableOutputs = @json($availableOutputs);
        const defaultSensors = @json($defaultSensors);
        const defaultOutputs = @json($defaultOutputs);
        let sensorCounter = 0;
        let outputCounter = 0;

        function getSensorOptions(selectedKey = '') {
            let options = '<option value="">-- Pilih Jenis Sensor --</option>';
            for (const [key, info] of Object.entries(availableSensors)) {
                const selected = key === selectedKey ? 'selected' : '';
                options += `<option value="${key}" ${selected}>${info.label} ${info.unit ? '(' + info.unit + ')' : ''}</option>`;
            }
            return options;
        }

        function getOutputOptions(selectedKey = '') {
            let options = '<option value="">-- Pilih Jenis Output --</option>';
            for (const [key, info] of Object.entries(availableOutputs)) {
                const selected = key === selectedKey ? 'selected' : '';
                const typeLabel = info.type === 'boolean' ? 'ON/OFF' : (info.type === 'percentage' ? '0-100%' : 'Angka');
                options += `<option value="${key}" ${selected}>${info.label} (${typeLabel})</option>`;
            }
            return options;
        }

        function addSensorRow(sensorKey = '', customLabel = '') {
            sensorCounter++;
            const container = document.getElementById('sensorContainer');
            const row = document.createElement('div');
            row.className = 'sensor-row';
            row.id = `sensorRow_${sensorCounter}`;
            row.innerHTML = `
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <select class="form-select sensor-select" name="sensors[${sensorCounter}][type]" required onchange="updateSubmitButton()">
                        ${getSensorOptions(sensorKey)}
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control sensor-label-input" name="sensors[${sensorCounter}][label]" 
                           placeholder="Label custom (opsional)" value="${customLabel}">
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSensorRow(${sensorCounter})" style="border-radius: 50%;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
            container.appendChild(row);
            updateSensorCount();
            updateSubmitButton();
            refreshAutomationSensorDropdowns(); // Refresh automation sensor dropdowns
        }

        function addOutputRow(outputKey = '', customLabel = '', autoMode = 'none', maxSchedules = 8, maxSectors = 1, sensorId = '') {
            outputCounter++;
            const container = document.getElementById('outputContainer');
            const row = document.createElement('div');
            row.className = 'sensor-row output-row';
            row.id = `outputRow_${outputCounter}`;

            const sensorOptions = getAddedSensorOptions(sensorId);
            const isTimeMode = ['time', 'time_days', 'time_days_sector'].includes(autoMode);
            const hasSector = autoMode === 'time_days_sector';

            row.innerHTML = `
            <div class="row align-items-end g-3 mb-2">
                <div class="col-md-3">
                    <label class="form-label small text-white-50">Output Type</label>
                    <select class="form-select output-select" name="outputs[${outputCounter}][type]" onchange="updateSubmitButton()">
                        ${getOutputOptions(outputKey)}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-white-50">Label (opsional)</label>
                    <input type="text" class="form-control output-label-input" name="outputs[${outputCounter}][label]" 
                           placeholder="Label custom" value="${customLabel}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-white-50">Automation Mode</label>
                    <select class="form-select" name="outputs[${outputCounter}][automation_mode]" 
                            onchange="toggleAutomationFields(${outputCounter}, this.value)">
                        <option value="none" ${autoMode === 'none' ? 'selected' : ''}>None</option>
                        <option value="time" ${autoMode === 'time' ? 'selected' : ''}>Time Only</option>
                        <option value="time_days" ${autoMode === 'time_days' ? 'selected' : ''}>Time + Days</option>
                        <option value="time_days_sector" ${autoMode === 'time_days_sector' ? 'selected' : ''}>Time + Days + Sector</option>
                        <option value="sensor" ${autoMode === 'sensor' ? 'selected' : ''}>Sensor</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-outline-danger" 
                            onclick="removeOutputRow(${outputCounter})" style="border-radius: 12px;">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
            <div class="row align-items-end g-3" id="automationRow_${outputCounter}">
                <div class="col-md-3 automation-time-fields" id="timeFields_${outputCounter}" style="display: ${isTimeMode ? 'block' : 'none'}">
                    <label class="form-label small text-white-50">Max Schedule Slots</label>
                    <input type="number" class="form-control" name="outputs[${outputCounter}][max_schedules]" 
                           value="${maxSchedules}" min="1" max="20">
                </div>
                <div class="col-md-3 automation-sector-fields" id="sectorFields_${outputCounter}" style="display: ${hasSector ? 'block' : 'none'}">
                    <label class="form-label small text-white-50">Jumlah Sektor</label>
                    <input type="number" class="form-control" name="outputs[${outputCounter}][max_sectors]" 
                           value="${maxSectors}" min="1" max="10">
                </div>
                <div class="col-md-4 automation-sensor-fields" id="sensorSelectFields_${outputCounter}" style="display: ${autoMode === 'sensor' ? 'block' : 'none'}">
                    <label class="form-label small text-white-50">Pilih Sensor</label>
                    <select class="form-select automation-sensor-select" name="outputs[${outputCounter}][automation_sensor_id]">
                        <option value="">-- Pilih Sensor --</option>
                        ${sensorOptions}
                    </select>
                </div>
            </div>
        `;
            container.appendChild(row);
            updateOutputCount();
        }

        function getAddedSensorOptions(selectedId = '') {
            const sensorRows = document.querySelectorAll('.sensor-row:not(.output-row)');
            let options = '';
            let sensorIndex = 1;

            sensorRows.forEach(row => {
                const select = row.querySelector('.sensor-select');
                const labelInput = row.querySelector('.sensor-label-input');

                if (select && select.value) {
                    const sensorType = select.value;
                    const sensorInfo = availableSensors[sensorType];
                    const customLabel = labelInput ? labelInput.value.trim() : '';
                    const label = customLabel || sensorInfo.label;
                    const value = `sensor_${sensorIndex}`;

                    options += `<option value="${value}" ${value == selectedId ? 'selected' : ''}>${label}</option>`;
                    sensorIndex++;
                }
            });

            return options;
        }

        function refreshAutomationSensorDropdowns() {
            const sensorOptions = getAddedSensorOptions();
            document.querySelectorAll('.automation-sensor-select').forEach(dropdown => {
                const currentValue = dropdown.value;
                dropdown.innerHTML = '<option value="">-- Select Sensor --</option>' + sensorOptions;
                dropdown.value = currentValue; // Restore selection if still valid
            });
        }

        function toggleAutomationFields(index, mode) {
            const timeFields = document.getElementById(`timeFields_${index}`);
            const sectorFields = document.getElementById(`sectorFields_${index}`);
            const sensorSelectFields = document.getElementById(`sensorSelectFields_${index}`);

            const isTimeMode = ['time', 'time_days', 'time_days_sector'].includes(mode);
            const hasSector = mode === 'time_days_sector';

            timeFields.style.display = isTimeMode ? 'block' : 'none';
            sectorFields.style.display = hasSector ? 'block' : 'none';
            sensorSelectFields.style.display = mode === 'sensor' ? 'block' : 'none';
        }

        function removeSensorRow(id) {
            const row = document.getElementById(`sensorRow_${id}`);
            if (row) {
                row.remove();
                updateSensorCount();
                updateSubmitButton();
                refreshAutomationSensorDropdowns(); // Refresh dropdowns after sensor removed
            }
        }

        function removeOutputRow(id) {
            const row = document.getElementById(`outputRow_${id}`);
            if (row) { row.remove(); updateOutputCount(); }
        }

        function updateSensorCount() {
            const count = document.querySelectorAll('.sensor-row:not(.output-row)').length;
            document.getElementById('sensorCount').textContent = count + ' sensor';
        }

        function updateOutputCount() {
            const count = document.querySelectorAll('.output-row').length;
            document.getElementById('outputCount').textContent = count + ' output';
        }

        function updateSubmitButton() {
            const typeSelected = document.getElementById('deviceType').value !== '';
            const sensorCount = document.querySelectorAll('.sensor-row:not(.output-row)').length;
            const allSensorsSelected = Array.from(document.querySelectorAll('.sensor-select')).every(s => s.value !== '');
            document.getElementById('submitBtn').disabled = !(typeSelected && sensorCount > 0 && allSensorsSelected);
        }

        function selectDeviceType(type) {
            document.querySelectorAll('.type-card').forEach(card => card.classList.remove('selected'));
            document.querySelector(`[data-type="${type}"]`).classList.add('selected');
            document.getElementById('deviceType').value = type;

            // Reset sensors
            document.getElementById('sensorContainer').innerHTML = '';
            sensorCounter = 0;

            // Reset outputs
            document.getElementById('outputContainer').innerHTML = '';
            outputCounter = 0;

            // Add default sensors
            if (defaultSensors[type]) {
                for (const [sensorKey, count] of Object.entries(defaultSensors[type])) {
                    for (let i = 0; i < count; i++) {
                        const label = count > 1 ? `${availableSensors[sensorKey].label} ${i + 1}` : '';
                        addSensorRow(sensorKey, label);
                    }
                }
            }

            // Add default outputs
            if (defaultOutputs[type]) {
                for (const [outputKey, count] of Object.entries(defaultOutputs[type])) {
                    for (let i = 0; i < count; i++) {
                        const label = count > 1 ? `${availableOutputs[outputKey].label} ${i + 1}` : '';
                        addOutputRow(outputKey, label);
                    }
                }
            }

            updateSubmitButton();
        }

        document.getElementById('deviceForm').addEventListener('submit', function (e) {
            const type = document.getElementById('deviceType').value;
            const sensors = document.querySelectorAll('.sensor-row:not(.output-row)').length;
            if (!type) { e.preventDefault(); alert('Pilih tipe alat terlebih dahulu!'); return false; }
            if (sensors === 0) { e.preventDefault(); alert('Tambahkan minimal 1 sensor!'); return false; }
        });
    </script>
</body>

</html>