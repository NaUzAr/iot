<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// --- IMPORT PENTING UNTUK DATABASE ---
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// -------------------------------------

class AdminDeviceController extends Controller
{
    // Helper: Pastikan yang akses adalah Admin
    private function checkAdmin()
    {
        // Pastikan kolom 'role' sudah ada di tabel users
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Admin.');
        }
    }

    // 1. HALAMAN LIST DEVICE (INDEX)
    public function index()
    {
        $this->checkAdmin();
        $devices = Device::all();
        return view('admin.index', compact('devices'));
    }

    // 2. HALAMAN FORM CREATE
    public function create()
    {
        $this->checkAdmin();

        // Device types
        $deviceTypes = [
            'aws' => 'Automatic Weather Station',
            'greenhouse' => 'Greenhouse Controller',
        ];

        // Available sensors
        $availableSensors = [
            'temperature' => ['label' => 'Suhu', 'unit' => '°C'],
            'humidity' => ['label' => 'Kelembaban Udara', 'unit' => '%'],
            'soil_moisture' => ['label' => 'Kelembaban Tanah', 'unit' => '%'],
            'rainfall' => ['label' => 'Curah Hujan', 'unit' => 'mm'],
            'wind_speed' => ['label' => 'Kecepatan Angin', 'unit' => 'm/s'],
            'wind_direction' => ['label' => 'Arah Angin', 'unit' => '°'],
            'pressure' => ['label' => 'Tekanan Udara', 'unit' => 'hPa'],
            'light' => ['label' => 'Intensitas Cahaya', 'unit' => 'lux'],
            'uv' => ['label' => 'UV Index', 'unit' => ''],
            'co2' => ['label' => 'CO2', 'unit' => 'ppm'],
            'ph' => ['label' => 'pH Tanah', 'unit' => ''],
            'ec' => ['label' => 'EC Tanah', 'unit' => 'mS/cm'],
        ];

        // Available outputs
        $availableOutputs = [
            'relay' => ['label' => 'Relay', 'type' => 'boolean', 'unit' => ''],
            'pump' => ['label' => 'Pompa Air', 'type' => 'boolean', 'unit' => ''],
            'fan' => ['label' => 'Kipas', 'type' => 'boolean', 'unit' => ''],
            'valve' => ['label' => 'Katup Air', 'type' => 'boolean', 'unit' => ''],
            'dimmer' => ['label' => 'Dimmer', 'type' => 'percentage', 'unit' => '%'],
            'servo' => ['label' => 'Servo', 'type' => 'number', 'unit' => '°'],
        ];

        // Default sensors per device type
        $defaultSensors = [
            'aws' => ['temperature' => 1, 'humidity' => 1, 'rainfall' => 1, 'wind_speed' => 1, 'wind_direction' => 1],
            'greenhouse' => ['temperature' => 1, 'humidity' => 1, 'soil_moisture' => 1, 'light' => 1],
        ];

        // Default outputs per device type
        $defaultOutputs = [
            'aws' => [],
            'greenhouse' => ['pump' => 1, 'fan' => 1],
        ];

        return view('admin.create_device', compact('deviceTypes', 'availableSensors', 'availableOutputs', 'defaultSensors', 'defaultOutputs'));
    }

    // 3. PROSES SIMPAN DEVICE BARU (STORE)
    public function store(Request $request)
    {
        $this->checkAdmin();

        // A. Validasi Input
        $request->validate([
            'name' => 'required|string|max:100',
            'mqtt_topic' => 'required|string|max:100',
            'type' => 'required|string|in:aws,greenhouse',
            'sensors' => 'required|array|min:1',
            'sensors.*.type' => 'required|string',
        ]);

        // Available sensor configs
        $availableSensors = [
            'temperature' => ['label' => 'Suhu', 'unit' => '°C'],
            'humidity' => ['label' => 'Kelembaban Udara', 'unit' => '%'],
            'soil_moisture' => ['label' => 'Kelembaban Tanah', 'unit' => '%'],
            'rainfall' => ['label' => 'Curah Hujan', 'unit' => 'mm'],
            'wind_speed' => ['label' => 'Kecepatan Angin', 'unit' => 'm/s'],
            'wind_direction' => ['label' => 'Arah Angin', 'unit' => '°'],
            'pressure' => ['label' => 'Tekanan Udara', 'unit' => 'hPa'],
            'light' => ['label' => 'Intensitas Cahaya', 'unit' => 'lux'],
            'uv' => ['label' => 'UV Index', 'unit' => ''],
            'co2' => ['label' => 'CO2', 'unit' => 'ppm'],
            'ph' => ['label' => 'pH Tanah', 'unit' => ''],
            'ec' => ['label' => 'EC Tanah', 'unit' => 'mS/cm'],
        ];

        // Available output configs
        $availableOutputs = [
            'relay' => ['label' => 'Relay', 'type' => 'boolean', 'unit' => ''],
            'pump' => ['label' => 'Pompa Air', 'type' => 'boolean', 'unit' => ''],
            'fan' => ['label' => 'Kipas', 'type' => 'boolean', 'unit' => ''],
            'valve' => ['label' => 'Katup Air', 'type' => 'boolean', 'unit' => ''],
            'dimmer' => ['label' => 'Dimmer', 'type' => 'percentage', 'unit' => '%'],
            'servo' => ['label' => 'Servo', 'type' => 'number', 'unit' => '°'],
        ];

        // B. Generate Token Unik & Nama Tabel
        $token = Str::random(16);
        $tableName = 'log_' . $token;

        // C. Process sensors from form
        $sensors = $request->sensors;
        $sensorColumns = [];
        $sensorCounter = [];

        foreach ($sensors as $sensor) {
            $type = $sensor['type'];
            if (!isset($availableSensors[$type]))
                continue;

            // Count duplicates to generate unique names
            if (!isset($sensorCounter[$type])) {
                $sensorCounter[$type] = 0;
            }
            $sensorCounter[$type]++;

            // Generate column name (e.g., temperature, temperature_2)
            $columnName = $sensorCounter[$type] > 1 ? "{$type}_{$sensorCounter[$type]}" : $type;

            // Custom label from form or default
            $label = !empty($sensor['label']) ? $sensor['label'] : $availableSensors[$type]['label'];
            if ($sensorCounter[$type] > 1 && empty($sensor['label'])) {
                $label .= " {$sensorCounter[$type]}";
            }

            $sensorColumns[] = [
                'name' => $columnName,
                'type' => $type,
                'label' => $label,
                'unit' => $availableSensors[$type]['unit'],
            ];
        }

        // D. BUAT TABEL LOG OTOMATIS dengan sensor columns
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($sensorColumns) {
                $table->id();
                foreach ($sensorColumns as $col) {
                    $table->float($col['name'])->nullable();
                }
                $table->timestamp('recorded_at')->useCurrent();
            });
        }

        // E. Simpan Device ke database
        $device = Device::create([
            'name' => $request->name,
            'mqtt_topic' => $request->mqtt_topic,
            'token' => $token,
            'table_name' => $tableName,
            'type' => $request->type,
        ]);

        // F. Simpan Sensors ke device_sensors
        foreach ($sensorColumns as $sensor) {
            \App\Models\DeviceSensor::create([
                'device_id' => $device->id,
                'sensor_name' => $sensor['name'],
                'sensor_label' => $sensor['label'],
                'unit' => $sensor['unit'],
            ]);
        }

        // G. Simpan Outputs ke device_outputs
        if ($request->has('outputs')) {
            foreach ($request->outputs as $output) {
                if (empty($output['type']))
                    continue;

                $type = $output['type'];
                if (!isset($availableOutputs[$type]))
                    continue;

                $outputConfig = $availableOutputs[$type];
                $label = !empty($output['label']) ? $output['label'] : $outputConfig['label'];

                \App\Models\DeviceOutput::create([
                    'device_id' => $device->id,
                    'output_name' => $type . '_' . uniqid(),
                    'output_label' => $label,
                    'output_type' => $outputConfig['type'],
                    'unit' => $outputConfig['unit'],
                    'automation_mode' => $output['automation_mode'] ?? 'none',
                    'max_schedules' => $output['max_schedules'] ?? 8,
                ]);
            }
        }

        // H. Redirect ke Halaman List Device
        return redirect()->route('admin.devices.index')
            ->with('success', "Sukses! Device '{$request->name}' berhasil dibuat dengan " . count($sensorColumns) . " sensor.");
    }

    // 4. HALAMAN FORM EDIT
    public function edit($id)
    {
        $this->checkAdmin();
        $device = Device::findOrFail($id);
        return view('admin.edit', compact('device'));
    }

    // 5. PROSES UPDATE DEVICE
    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
            'mqtt_topic' => 'required|string|max:100',
        ]);

        $device = Device::findOrFail($id);

        $device->update([
            'name' => $request->name,
            'mqtt_topic' => $request->mqtt_topic,
            // Token & table_name JANGAN diupdate agar koneksi database aman
        ]);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Data device berhasil diperbarui!');
    }

    // 6. PROSES HAPUS DEVICE (DESTROY)
    public function destroy($id)
    {
        $this->checkAdmin();
        $device = Device::findOrFail($id);

        // A. Hapus Tabel Log fisiknya dari database (PENTING!)
        // Hati-hati, data sensor akan hilang permanen
        Schema::dropIfExists($device->table_name);

        // B. Hapus data dari tabel devices
        $device->delete();

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device dan Tabel Log berhasil dihapus permanen.');
    }

    // 7. HALAMAN MONITORING DEVICE (ADMIN VIEW)
    public function showMonitoring($id)
    {
        $this->checkAdmin();
        $isAdminView = true;

        $device = Device::with(['sensors', 'outputs'])->findOrFail($id);
        $sensors = $device->sensors;
        $outputs = $device->outputs;

        // Default values
        $logData = collect();
        $chartData = collect();
        $latestData = null;

        if ($device->table_name && Schema::hasTable($device->table_name)) {
            // Ambil 50 data terbaru untuk chart
            $chartData = \DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            // Ambil data untuk tabel dengan pagination (20 per halaman)
            $logData = \DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->paginate(20);

            // Ambil data terbaru untuk display sensor cards
            $latestData = \DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->first();
        } else {
            // Buat paginator kosong jika tidak ada data
            $logData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('monitoring.show', compact('device', 'sensors', 'outputs', 'logData', 'chartData', 'latestData', 'isAdminView'));
    }

    // 8. TOGGLE OUTPUT (ADMIN - uses device_id directly)
    public function toggleOutput(Request $request, $deviceId, $outputId)
    {
        $this->checkAdmin();

        $device = Device::findOrFail($deviceId);

        // Ambil output dari device ini
        $output = \App\Models\DeviceOutput::where('id', $outputId)
            ->where('device_id', $device->id)
            ->firstOrFail();

        // Validasi request
        $request->validate([
            'value' => 'required',
        ]);

        $newValue = $request->value;

        // Untuk boolean, konversi ke 0 atau 1
        if ($output->output_type === 'boolean') {
            $newValue = filter_var($newValue, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        } else {
            $newValue = (float) $newValue;
        }

        // Update current_value di database
        $output->current_value = $newValue;
        $output->save();

        // Publish ke MQTT untuk kirim perintah ke device
        try {
            $topic = $device->mqtt_topic . '/control';

            // Format simpel: <output#value>
            $message = sprintf('<%s#%s>', $output->output_name, $newValue);

            // MQTT Connection
            $host = config('mqtt.host', env('MQTT_HOST', 'smartagri.web.id'));
            $port = config('mqtt.port', env('MQTT_PORT', 1883));
            $username = config('mqtt.username', env('MQTT_USERNAME'));
            $password = config('mqtt.password', env('MQTT_PASSWORD'));

            $connectionSettings = new \PhpMqtt\Client\ConnectionSettings();
            if ($username && $password) {
                $connectionSettings = $connectionSettings
                    ->setUsername($username)
                    ->setPassword($password);
            }
            $connectionSettings = $connectionSettings
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(10);

            $mqtt = new \PhpMqtt\Client\MqttClient($host, $port, 'laravel-admin-control-' . uniqid());
            $mqtt->connect($connectionSettings, true);
            $mqtt->publish($topic, $message, 1);
            $mqtt->disconnect();

            \Log::info("MQTT Admin Output Control sent", ['topic' => $topic, 'message' => $message]);
        } catch (\Exception $e) {
            \Log::error("MQTT Admin Output Control failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'output_id' => $output->id,
            'output_name' => $output->output_name,
            'new_value' => $newValue,
            'message' => "Output {$output->output_label} berhasil diupdate!",
        ]);
    }
}