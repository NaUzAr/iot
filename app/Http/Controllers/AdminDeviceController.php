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
        ]);

        // B. Generate Token Unik & Nama Tabel
        $token = Str::random(16); // Token acak 16 karakter
        $tableName = 'log_' . $token; // Nama tabel: log_x8s7d...

        // C. BUAT TABEL LOG OTOMATIS (Schema Builder)
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                // Sesuaikan kolom ini dengan sensor Weather Station kamu
                $table->float('temperature')->nullable();
                $table->float('humidity')->nullable();
                $table->float('rainfall')->nullable();
                $table->timestamp('recorded_at')->useCurrent();
            });
        }

        // D. Simpan Metadata ke Tabel Devices
        Device::create([
            'name' => $request->name,
            'mqtt_topic' => $request->mqtt_topic,
            'token' => $token,
            'table_name' => $tableName,
        ]);

        // E. Redirect ke Halaman List Device (Bukan Dashboard)
        return redirect()->route('admin.devices.index')
            ->with('success', "Sukses! Device '$request->name' berhasil dibuat dan Tabel Log siap.");
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