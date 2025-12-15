<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Automation - {{ $device->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-green);
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .btn-outline-light {
            border-color: var(--glass-border);
        }

        .type-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .type-option {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .type-option:hover {
            border-color: var(--primary-green);
        }

        .type-option.active {
            background: rgba(34, 197, 94, 0.2);
            border-color: var(--primary-green);
        }

        .type-option input[type="radio"] {
            display: none;
        }

        .type-option i {
            font-size: 2rem;
            color: var(--primary-green);
        }

        .type-option h5 {
            color: #fff;
            margin-top: 0.5rem;
        }

        .type-option p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin: 0;
        }

        .day-picker {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .day-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .day-btn.active {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: #fff;
        }

        .sensor-condition {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .hidden {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="text-white mb-1">
                        <i class="bi bi-plus-circle me-2"></i>Create Automation
                    </h3>
                    <p class="text-white-50 mb-0">{{ $device->name }}</p>
                </div>
                <a href="{{ route('automation.index', $device->id) }}" class="btn btn-outline-light">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('automation.store', $device->id) }}" method="POST" id="automationForm">
                @csrf

                <!-- Automation Name -->
                <div class="mb-3">
                    <label class="form-label">Automation Name</label>
                    <input type="text" name="automation_name" class="form-control" required
                        value="{{ old('automation_name') }}" placeholder="e.g., Weekday Watering">
                </div>

                <!-- Output Selection -->
                <div class="mb-3">
                    <label class="form-label">Control Output</label>
                    <select name="device_output_id" class="form-select" required>
                        <option value="">-- Select Output --</option>
                        @foreach($device->outputs as $output)
                            <option value="{{ $output->id }}" {{ old('device_output_id') == $output->id ? 'selected' : '' }}>
                                {{ $output->output_label }} ({{ $output->output_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Selector -->
                <div class="type-selector">
                    @if($canAddTime)
                        <label class="type-option active" id="timeOption">
                            <input type="radio" name="automation_type" value="time" checked>
                            <i class="bi bi-clock-history"></i>
                            <h5>Time-based</h5>
                            <p>Schedule based on time</p>
                        </label>
                    @endif

                    @if($canAddSensor)
                        <label class="type-option {{ !$canAddTime ? 'active' : '' }}" id="sensorOption">
                            <input type="radio" name="automation_type" value="sensor" {{ !$canAddTime ? 'checked' : '' }}>
                            <i class="bi bi-speedometer"></i>
                            <h5>Sensor-based</h5>
                            <p>Trigger based on sensor values</p>
                        </label>
                    @endif
                </div>

                <!-- Time-based Fields -->
                <div id="timeFields" class="{{ $canAddTime ? '' : 'hidden' }}">
                    <div class="mb-3">
                        <label class="form-label">Days</label>
                        <div class="day-picker">
                            <div class="day-btn" data-day="0">Sun</div>
                            <div class="day-btn" data-day="1">Mon</div>
                            <div class="day-btn" data-day="2">Tue</div>
                            <div class="day-btn" data-day="3">Wed</div>
                            <div class="day-btn" data-day="4">Thu</div>
                            <div class="day-btn" data-day="5">Fri</div>
                            <div class="day-btn" data-day="6">Sat</div>
                        </div>
                        <input type="hidden" name="schedule_days" id="scheduleDays">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="schedule_time_start" class="form-control"
                                value="{{ old('schedule_time_start', '06:00') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="schedule_time_end" class="form-control"
                                value="{{ old('schedule_time_end', '18:00') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Output Value (when active)</label>
                        <input type="number" name="schedule_value" class="form-control" step="0.1"
                            value="{{ old('schedule_value', 1) }}">
                    </div>
                </div>

                <!-- Sensor-based Fields -->
                <div id="sensorFields" class="hidden">
                    <div class="mb-3">
                        <label class="form-label">Sensor Conditions (1-3 sensors)</label>
                        <div id="sensorConditions">
                            <div class="sensor-condition">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <select name="sensor_conditions[0][sensor_id]" class="form-select" required>
                                            <option value="">-- Select Sensor --</option>
                                            @foreach($device->sensors as $sensor)
                                                <option value="{{ $sensor->id }}" data-name="{{ $sensor->sensor_name }}">
                                                    {{ $sensor->sensor_label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="sensor_conditions[0][sensor_name]"
                                            class="sensor-name-input">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select name="sensor_conditions[0][operator]" class="form-select" required>
                                            <option value=">">></option>
                                            <option value="<">
                                                << /option>
                                            <option value=">=">>=</option>
                                            <option value="<=">
                                                <=< /option>
                                            <option value="==">==</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="number" name="sensor_conditions[0][threshold]" class="form-control"
                                            placeholder="Threshold" step="0.1" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="number" name="sensor_conditions[0][hysteresis]"
                                            class="form-control" placeholder="Hysteresis" step="0.1" value="2">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light mt-2" id="addConditionBtn">
                            <i class="bi bi-plus-circle"></i> Add Sensor Condition
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logic (for multiple sensors)</label>
                        <select name="condition_logic" class="form-select">
                            <option value="AND">AND (all conditions must be true)</option>
                            <option value="OR">OR (any condition can be true)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action Value (when condition met)</label>
                        <input type="number" name="action_value" class="form-control" step="0.1" value="1">
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('automation.index', $device->id) }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Create Automation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Type switcher
        const timeOption = document.getElementById('timeOption');
        const sensorOption = document.getElementById('sensorOption');
        const timeFields = document.getElementById('timeFields');
        const sensorFields = document.getElementById('sensorFields');

        if (timeOption) {
            timeOption.addEventListener('click', function () {
                timeOption.classList.add('active');
                sensorOption?.classList.remove('active');
                timeFields.classList.remove('hidden');
                sensorFields.classList.add('hidden');
            });
        }

        if (sensorOption) {
            sensorOption.addEventListener('click', function () {
                sensorOption.classList.add('active');
                timeOption?.classList.remove('active');
                sensorFields.classList.remove('hidden');
                timeFields.classList.add('hidden');
            });
        }

        // Day picker
        const selectedDays = new Set();
        document.querySelectorAll('.day-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const day = this.dataset.day;
                if (selectedDays.has(day)) {
                    selectedDays.delete(day);
                    this.classList.remove('active');
                } else {
                    selectedDays.add(day);
                    this.classList.add('active');
                }
                document.getElementById('scheduleDays').value = JSON.stringify([...selectedDays].map(Number));
            });
        });

        // Sensor condition builder
        let conditionIndex = 1;
        document.getElementById('addConditionBtn').addEventListener('click', function () {
            if (conditionIndex >= 3) {
                alert('Maximum 3 sensor conditions allowed');
                return;
            }

            const conditionsDiv = document.getElementById('sensorConditions');
            const newCondition = conditionsDiv.firstElementChild.cloneNode(true);

            // Update input names
            newCondition.querySelectorAll('input, select').forEach(input => {
                const name = input.name;
                if (name) {
                    input.name = name.replace('[0]', `[${conditionIndex}]`);
                    if (input.type !== 'hidden' && input.type !== 'select-one') {
                        input.value = input.placeholder === 'Hysteresis' ? '2' : '';
                    }
                }
            });

            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2';
            removeBtn.innerHTML = '<i class="bi bi-x-circle"></i>';
            removeBtn.onclick = function () { this.parentElement.remove(); };
            newCondition.style.position = 'relative';
            newCondition.appendChild(removeBtn);

            conditionsDiv.appendChild(newCondition);
            conditionIndex++;
        });

        // Auto-populate sensor_name from sensor selection
        document.addEventListener('change', function (e) {
            if (e.target.matches('select[name^="sensor_conditions"]') && e.target.name.includes('sensor_id')) {
                const option = e.target.options[e.target.selectedIndex];
                const sensorName = option.dataset.name;
                const hiddenInput = e.target.parentElement.querySelector('.sensor-name-input');
                if (hiddenInput && sensorName) {
                    hiddenInput.value = sensorName;
                }
            }
        });
    </script>
</body>

</html>