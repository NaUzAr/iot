<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceSensor;
use App\Models\DeviceOutput;
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
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Admin.');
        }
    }

    // 1. HALAMAN LIST DEVICE (INDEX)
    public function index()
    {
        $this->checkAdmin();
        $devices = Device::with(['sensors', 'outputs'])->get();
        return view('admin.index', compact('devices'));
    }

    // 2. HALAMAN FORM CREATE
    public function create()
    {
        $this->checkAdmin();

        // Kirim data konfigurasi ke view
        $deviceTypes = Device::getDeviceTypes();
        $availableSensors = Device::getAvailableSensors();
        $availableOutputs = Device::getAvailableOutputs();
        $defaultSensors = [];
        $defaultOutputs = [];
        foreach (Device::getDeviceTypes() as $type => $label) {
            $defaultSensors[$type] = Device::getDefaultSensorsForType($type);
            $defaultOutputs[$type] = Device::getDefaultOutputsForType($type);
        }

        return view('admin.create_device', compact('deviceTypes', 'availableSensors', 'availableOutputs', 'defaultSensors', 'defaultOutputs'));
    }

    // 3. PROSES SIMPAN DEVICE BARU (STORE)
    public function store(Request $request)
    {
        $this->checkAdmin();

        // A. Validasi Input
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'mqtt_topic' => 'required|string|max:100',
            'sensors' => 'required|array|min:1',
            'sensors.*.type' => 'required|string',
        ], [
            'sensors.required' => 'Tambahkan minimal 1 sensor!',
            'sensors.min' => 'Tambahkan minimal 1 sensor!',
            'sensors.*.type.required' => 'Pilih jenis sensor untuk setiap baris!',
        ]);

        // B. Generate Token Unik & Nama Tabel
        $token = Str::random(16);
        $tableName = 'log_' . $token;

        // C. Proses sensor - buat nama kolom unik untuk sensor dengan jenis sama
        $sensorData = $request->sensors;
        $availableSensors = Device::getAvailableSensors();

        // Hitung kemunculan setiap jenis sensor untuk membuat nama unik
        $sensorCounts = [];
        $processedSensors = [];

        foreach ($sensorData as $sensor) {
            $sensorType = $sensor['type'];
            $customLabel = $sensor['label'] ?? '';

            // Increment counter untuk tipe ini
            if (!isset($sensorCounts[$sensorType])) {
                $sensorCounts[$sensorType] = 0;
            }
            $sensorCounts[$sensorType]++;

            // Buat nama kolom unik
            $columnName = $sensorType;
            if ($sensorCounts[$sensorType] > 1 || $this->countSensorType($sensorData, $sensorType) > 1) {
                $columnName = $sensorType . '_' . $sensorCounts[$sensorType];
            }

            // Buat label
            $sensorInfo = $availableSensors[$sensorType] ?? ['label' => $sensorType, 'unit' => ''];
            $label = $customLabel ?: $sensorInfo['label'];
            if ($this->countSensorType($sensorData, $sensorType) > 1 && empty($customLabel)) {
                $label = $sensorInfo['label'] . ' ' . $sensorCounts[$sensorType];
            }

            $processedSensors[] = [
                'column_name' => $columnName,
                'sensor_type' => $sensorType,
                'label' => $label,
                'unit' => $sensorInfo['unit'] ?? '',
            ];
        }

        // D. BUAT TABEL LOG OTOMATIS dengan kolom sesuai sensor
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($processedSensors) {
                $table->id();

                // Buat kolom untuk setiap sensor
                foreach ($processedSensors as $sensor) {
                    $table->float($sensor['column_name'])->nullable();
                }

                $table->timestamp('recorded_at')->useCurrent();
            });
        }

        // E. Simpan Metadata ke Tabel Devices
        $device = Device::create([
            'name' => $request->name,
            'type' => $request->type,
            'mqtt_topic' => $request->mqtt_topic,
            'token' => $token,
            'table_name' => $tableName,
            'max_time_schedules' => $request->max_time_schedules ?? 5,
            'max_sensor_automations' => $request->max_sensor_automations ?? 3,
        ]);

        // F. Simpan Konfigurasi Sensor ke Tabel device_sensors
        $createdSensors = [];
        foreach ($processedSensors as $sensor) {
            $createdSensor = DeviceSensor::create([
                'device_id' => $device->id,
                'sensor_name' => $sensor['column_name'],  // Nama kolom di tabel log
                'sensor_label' => $sensor['label'],
                'unit' => $sensor['unit'],
            ]);
            $createdSensors[] = $createdSensor; // Store for later reference
        }

        // G. Proses & Simpan Output (jika ada)
        $outputData = $request->outputs ?? [];
        $availableOutputs = Device::getAvailableOutputs();
        $outputCounts = [];
        $processedOutputs = [];

        foreach ($outputData as $origIndex => $output) {
            if (empty($output['type']))
                continue;

            $outputType = $output['type'];
            $customLabel = $output['label'] ?? '';

            // Increment counter untuk tipe ini
            if (!isset($outputCounts[$outputType])) {
                $outputCounts[$outputType] = 0;
            }
            $outputCounts[$outputType]++;

            // Buat nama output unik
            $outputName = $outputType;
            if ($outputCounts[$outputType] > 1 || $this->countOutputType($outputData, $outputType) > 1) {
                $outputName = $outputType . '_' . $outputCounts[$outputType];
            }

            // Buat label
            $outputInfo = $availableOutputs[$outputType] ?? ['label' => $outputType, 'type' => 'boolean', 'unit' => ''];
            $label = $customLabel ?: $outputInfo['label'];
            if ($this->countOutputType($outputData, $outputType) > 1 && empty($customLabel)) {
                $label = $outputInfo['label'] . ' ' . $outputCounts[$outputType];
            }

            $processedOutputs[] = [
                'output_name' => $outputName,
                'output_type' => $outputInfo['type'],
                'label' => $label,
                'unit' => $outputInfo['unit'] ?? '',
                'orig_index' => $origIndex, // Store original index for correct mapping
            ];
        }

        // Simpan Konfigurasi Output ke Tabel device_outputs
        foreach ($processedOutputs as $output) {
            $origIndex = $output['orig_index'];

            // Resolve automation_sensor_id
            $automationSensorId = null;
            if (($outputData[$origIndex]['automation_mode'] ?? 'none') === 'sensor') {
                $sensorIndexStr = $outputData[$origIndex]['automation_sensor_id'] ?? null;
                if ($sensorIndexStr) {
                    // Extract numeric index from "sensor_1", "sensor_2", etc.
                    preg_match('/sensor_(\d+)/', $sensorIndexStr, $matches);
                    if (isset($matches[1])) {
                        $sensorIndex = (int) $matches[1] - 1; // Convert to 0-based index
                        if (isset($createdSensors[$sensorIndex])) {
                            $automationSensorId = $createdSensors[$sensorIndex]->id;
                        }
                    }
                }
            }

            DeviceOutput::create([
                'device_id' => $device->id,
                'output_name' => $output['output_name'],
                'output_label' => $output['label'],
                'output_type' => $output['output_type'],
                'unit' => $output['unit'],
                'default_value' => 0,
                'current_value' => 0,
                'automation_mode' => $outputData[$origIndex]['automation_mode'] ?? 'none',
                'max_schedules' => ($outputData[$origIndex]['automation_mode'] ?? 'none') === 'time'
                    ? ($outputData[$origIndex]['max_schedules'] ?? 8)
                    : null,
                'automation_sensor_id' => $automationSensorId,
            ]);
        }

        // H. Redirect ke Halaman List Device
        $outputCount = count($processedOutputs);
        $message = "Sukses! Device '$request->name' berhasil dibuat dengan " . count($processedSensors) . " sensor";
        if ($outputCount > 0) {
            $message .= " dan {$outputCount} output";
        }
        $message .= ".";

        return redirect()->route('admin.devices.index')
            ->with('success', $message);
    }

    // Helper: Hitung berapa kali sensor type muncul dalam array
    private function countSensorType($sensors, $type)
    {
        $count = 0;
        foreach ($sensors as $sensor) {
            if ($sensor['type'] === $type) {
                $count++;
            }
        }
        return $count;
    }

    // Helper: Hitung berapa kali output type muncul dalam array
    private function countOutputType($outputs, $type)
    {
        $count = 0;
        foreach ($outputs as $output) {
            if (isset($output['type']) && $output['type'] === $type) {
                $count++;
            }
        }
        return $count;
    }

    // 4. HALAMAN FORM EDIT
    public function edit($id)
    {
        $this->checkAdmin();
        $device = Device::with(['sensors', 'outputs'])->findOrFail($id);

        // Kirim data konfigurasi ke view
        $deviceTypes = Device::getDeviceTypes();
        $availableSensors = Device::getAvailableSensors();
        $availableOutputs = Device::getAvailableOutputs();

        return view('admin.edit', compact('device', 'deviceTypes', 'availableSensors', 'availableOutputs'));
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
            'max_time_schedules' => $request->max_time_schedules ?? $device->max_time_schedules,
            'max_sensor_automations' => $request->max_sensor_automations ?? $device->max_sensor_automations,
            // Token, table_name & type JANGAN diupdate agar koneksi database aman
        ]);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Data device berhasil diperbarui!');
    }

    // 6. PROSES HAPUS DEVICE (DESTROY)
    public function destroy($id)
    {
        $this->checkAdmin();
        $device = Device::findOrFail($id);

        // A. Hapus Tabel Log fisiknya dari database
        Schema::dropIfExists($device->table_name);

        // B. Hapus data dari tabel devices (sensors akan terhapus otomatis via cascade)
        $device->delete();

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device dan Tabel Log berhasil dihapus permanen.');
    }
}