<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes untuk menerima data sensor dari device IoT
| Dan routes untuk authentication Flutter app
|
*/

// ==================== AUTH ROUTES (Flutter) ====================
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Device routes untuk Flutter
    Route::get('/devices', function () {
        return response()->json([
            'success' => true,
            'data' => auth()->user()->userDevices()->with(['device.sensors', 'device.outputs'])->get()
        ]);
    });

    // POST - Tambah device via token
    Route::post('/devices', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required|string|size:16',
            'custom_name' => 'nullable|string|max:100',
        ]);

        // Cari device berdasarkan token
        $device = \App\Models\Device::where('token', $request->token)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan! Pastikan token benar.'
            ], 404);
        }

        // Cek apakah user sudah punya device ini
        $exists = \App\Models\UserDevice::where('user_id', auth()->id())
            ->where('device_id', $device->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Device ini sudah ada di daftar monitoring Anda.'
            ], 409);
        }

        // Simpan ke user_devices
        $userDevice = \App\Models\UserDevice::create([
            'user_id' => auth()->id(),
            'device_id' => $device->id,
            'custom_name' => $request->custom_name ?: $device->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Device '{$device->name}' berhasil ditambahkan!",
            'data' => $userDevice->load('device')
        ], 201);
    });

    // DELETE - Hapus device dari monitoring
    Route::delete('/devices/{id}', function ($id) {
        $userDevice = \App\Models\UserDevice::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$userDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.'
            ], 404);
        }

        $deviceName = $userDevice->custom_name;
        $userDevice->delete();

        return response()->json([
            'success' => true,
            'message' => "Device '{$deviceName}' berhasil dihapus."
        ]);
    });

    // GET - Detail device dengan sensors dan outputs
    Route::get('/devices/{id}', function ($id) {
        $userDevice = \App\Models\UserDevice::with(['device.sensors', 'device.outputs'])
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$userDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $userDevice
        ]);
    });

    // GET - Data sensor terbaru untuk device
    Route::get('/devices/{id}/sensor-data', function ($id) {
        $userDevice = \App\Models\UserDevice::with(['device.sensors'])
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$userDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.'
            ], 404);
        }

        $device = $userDevice->device;
        $tableName = $device->table_name;

        // Cek apakah tabel ada
        if (!$tableName || !\Schema::hasTable($tableName)) {
            return response()->json([
                'success' => true,
                'device' => $device->name,
                'sensors' => $device->sensors,
                'latest_data' => null,
                'chart_data' => [],
                'message' => 'Belum ada data sensor.'
            ]);
        }

        // Ambil data terbaru
        $latestData = \Illuminate\Support\Facades\DB::table($tableName)
            ->orderBy('recorded_at', 'desc')
            ->first();

        // Ambil 50 data terakhir untuk chart
        $chartData = \Illuminate\Support\Facades\DB::table($tableName)
            ->orderBy('recorded_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'device' => $device->name,
            'sensors' => $device->sensors,
            'latest_data' => $latestData,
            'chart_data' => $chartData
        ]);
    });

    // POST - Kontrol output device (relay, pump, etc)
    Route::post('/devices/{id}/outputs', function (\Illuminate\Http\Request $request, $id) {
        $request->validate([
            'output_name' => 'required|string',
            'value' => 'required',
        ]);

        $userDevice = \App\Models\UserDevice::with(['device.outputs'])
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$userDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.'
            ], 404);
        }

        $device = $userDevice->device;
        $output = $device->outputs()->where('output_name', $request->output_name)->first();

        if (!$output) {
            return response()->json([
                'success' => false,
                'message' => 'Output tidak ditemukan.'
            ], 404);
        }

        // Update nilai output
        $newValue = $output->output_type === 'boolean'
            ? (filter_var($request->value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0)
            : (float) $request->value;

        $output->current_value = $newValue;
        $output->save();

        // TODO: Publish ke MQTT untuk kirim ke device fisik

        return response()->json([
            'success' => true,
            'message' => "Output {$output->output_label} berhasil diupdate!",
            'output' => [
                'name' => $output->output_name,
                'label' => $output->output_label,
                'value' => $newValue
            ]
        ]);
    });

    // GET - MQTT configuration for device
    Route::get('/devices/{id}/mqtt', function ($id) {
        $userDevice = \App\Models\UserDevice::with(['device'])
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$userDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.'
            ], 404);
        }

        $device = $userDevice->device;

        return response()->json([
            'success' => true,
            'mqtt' => [
                'broker' => config('mqtt.host', env('MQTT_HOST', 'smartagri.web.id')),
                'port' => config('mqtt.port', env('MQTT_PORT', 1883)),
                'websocket_port' => 9001,
                'topic_data' => $device->mqtt_topic,
                'topic_control' => $device->mqtt_topic . '/control',
                'topic_status' => $device->mqtt_topic . '/status',
                'device_token' => $device->token,
            ]
        ]);
    });
});

// ==================== SENSOR DATA ROUTES (IoT Device) ====================
// POST /api/sensor-data - Terima data sensor baru
Route::post('/sensor-data', [SensorDataController::class, 'store']);

// GET /api/sensor-data/{token} - Ambil data terbaru
Route::get('/sensor-data/{token}', [SensorDataController::class, 'show']);
