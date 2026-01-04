<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\UserDevice;
use App\Models\DeviceOutput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Halaman utama monitoring - list device user
     */
    public function index()
    {
        $userDevices = UserDevice::with(['device.sensors'])
            ->where('user_id', Auth::id())
            ->get();

        return view('monitoring.index', compact('userDevices'));
    }

    /**
     * Form tambah device via token
     */
    public function create()
    {
        return view('monitoring.add_device');
    }

    /**
     * Proses tambah device via token
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:16',
            'custom_name' => 'nullable|string|max:100',
        ], [
            'token.required' => 'Token wajib diisi!',
            'token.size' => 'Token harus 16 karakter!',
        ]);

        // Cari device berdasarkan token
        $device = Device::where('token', $request->token)->first();

        if (!$device) {
            return back()->withErrors(['token' => 'Token tidak ditemukan! Pastikan token benar.'])->withInput();
        }

        // Cek apakah user sudah punya device ini
        $exists = UserDevice::where('user_id', Auth::id())
            ->where('device_id', $device->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['token' => 'Device ini sudah ada di daftar monitoring Anda.'])->withInput();
        }

        // Simpan ke user_devices
        UserDevice::create([
            'user_id' => Auth::id(),
            'device_id' => $device->id,
            'custom_name' => $request->custom_name ?: $device->name,
        ]);

        return redirect()->route('monitoring.index')
            ->with('success', "Device '{$device->name}' berhasil ditambahkan ke monitoring!");
    }

    /**
     * Halaman monitoring device - tampilkan data sensor
     */
    public function show(Request $request, $id)
    {
        // Pastikan user punya akses ke device ini
        $userDevice = UserDevice::with(['device.sensors', 'device.outputs'])
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $device = $userDevice->device;
        $sensors = $device->sensors;
        $outputs = $device->outputs;

        // Default values
        $logData = collect();
        $chartData = collect();
        $latestData = null;

        if ($device->table_name && \Schema::hasTable($device->table_name)) {
            // Ambil 50 data terbaru untuk chart (tidak di-paginate)
            $chartData = DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            // Ambil data untuk tabel dengan pagination (20 per halaman)
            $logData = DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->paginate(20);

            // Ambil data terbaru untuk display sensor cards
            $latestData = DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->first();
        } else {
            // Buat paginator kosong jika tidak ada data
            $logData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('monitoring.show', compact('userDevice', 'device', 'sensors', 'outputs', 'logData', 'chartData', 'latestData'));
    }

    /**
     * Hapus device dari monitoring user
     */
    public function destroy($id)
    {
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $deviceName = $userDevice->custom_name;
        $userDevice->delete();

        return redirect()->route('monitoring.index')
            ->with('success', "Device '{$deviceName}' berhasil dihapus dari monitoring.");
    }

    /**
     * Export data sensor ke CSV
     */
    public function exportCsv(Request $request, $id)
    {
        // Validasi user punya akses
        $userDevice = UserDevice::with(['device.sensors'])
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $device = $userDevice->device;
        $sensors = $device->sensors;

        // Validasi tanggal
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date . ' 00:00:00';
        $endDate = $request->end_date . ' 23:59:59';

        // Ambil data dari database
        if (!$device->table_name || !\Schema::hasTable($device->table_name)) {
            return back()->with('error', 'Tidak ada data untuk diexport.');
        }

        $data = DB::table($device->table_name)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data pada rentang tanggal tersebut.');
        }

        // Generate CSV
        $filename = 'sensor_data_' . $device->token . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data, $sensors) {
            $file = fopen('php://output', 'w');

            // Header row
            $headerRow = ['No', 'Waktu'];
            foreach ($sensors as $sensor) {
                $headerRow[] = $sensor->sensor_label . ' (' . $sensor->unit . ')';
            }
            fputcsv($file, $headerRow);

            // Data rows
            $no = 1;
            foreach ($data as $row) {
                $dataRow = [$no++, $row->recorded_at];
                foreach ($sensors as $sensor) {
                    $dataRow[] = $row->{$sensor->sensor_name} ?? '';
                }
                fputcsv($file, $dataRow);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Toggle output state (AJAX endpoint)
     */
    public function toggleOutput(Request $request, $userDeviceId, $outputId)
    {
        // Validasi user punya akses ke device ini
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('id', $userDeviceId)
            ->with('device')
            ->firstOrFail();

        // Ambil output dari device ini
        $output = DeviceOutput::where('id', $outputId)
            ->where('device_id', $userDevice->device_id)
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
            $device = $userDevice->device;
            $topic = $device->mqtt_topic . '/control';

            $message = [
                'type' => 'set_output',
                'token' => $device->token,
                'output' => $output->output_name,
                'value' => $newValue,
                'timestamp' => now()->toIso8601String(),
            ];

            // MQTT Connection
            $host = config('mqtt.host', env('MQTT_HOST', 'broker.hivemq.com'));
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

            $mqtt = new \PhpMqtt\Client\MqttClient($host, $port, 'laravel-control-' . uniqid());
            $mqtt->connect($connectionSettings, true);
            $mqtt->publish($topic, json_encode($message), 1);
            $mqtt->disconnect();

            \Log::info("MQTT Output Control sent", ['topic' => $topic, 'message' => $message]);
        } catch (\Exception $e) {
            \Log::error("MQTT Output Control failed: " . $e->getMessage());
            // Continue anyway, database already updated
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
