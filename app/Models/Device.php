<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    // Izinkan kolom ini diisi
    protected $fillable = [
        'name',
        'type',
        'mqtt_topic',
        'token',
        'table_name',
        'max_time_schedules',
        'max_sensor_automations'
    ];

    /**
     * Relasi ke DeviceSensor (satu device punya banyak sensor)
     */
    public function sensors()
    {
        return $this->hasMany(DeviceSensor::class);
    }

    /**
     * Relasi ke DeviceOutput (satu device punya banyak output)
     */
    public function outputs()
    {
        return $this->hasMany(DeviceOutput::class);
    }

    /**
     * Relasi ke OutputAutomationConfig (satu device punya banyak automation configs)
     */
    public function automationConfigs()
    {
        return $this->hasManyThrough(
            OutputAutomationConfig::class,
            DeviceOutput::class,
            'device_id',
            'device_output_id'
        );
    }

    /**
     * Check if device can add more time-based schedules
     */
    public function canAddTimeSchedule(): bool
    {
        $currentCount = $this->automationConfigs()
            ->where('automation_type', 'time')
            ->count();

        return $currentCount < $this->max_time_schedules;
    }

    /**
     * Check if device can add more sensor-based automations
     */
    public function canAddSensorAutomation(): bool
    {
        $currentCount = $this->automationConfigs()
            ->where('automation_type', 'sensor')
            ->count();

        return $currentCount < $this->max_sensor_automations;
    }

    /**
     * =====================================================
     * KONFIGURASI TIPE ALAT DAN SENSOR
     * Mudah diedit: Tambahkan tipe alat baru di sini
     * =====================================================
     */

    /**
     * Daftar Tipe Alat yang Tersedia
     * Tambahkan tipe baru dengan format: 'key' => 'Label Tampilan'
     */
    public static function getDeviceTypes(): array
    {
        return [
            'aws' => 'AWS (Automatic Weather Station)',
            'smart_gh' => 'Smart GH (Smart Greenhouse)',
            // Tambahkan tipe baru di sini:
            // 'water_quality' => 'Water Quality Sensor',
            // 'air_quality' => 'Air Quality Sensor',
        ];
    }

    /**
     * Daftar Preset Sensor yang Tersedia
     * Format: 'sensor_key' => ['label' => 'Label', 'unit' => 'satuan', 'icon' => 'bi-icon']
     * Sensor key harus lowercase tanpa spasi (digunakan sebagai prefix nama kolom)
     */
    public static function getAvailableSensors(): array
    {
        return [
            'temperature' => ['label' => 'Suhu (Temperature)', 'unit' => '°C', 'icon' => 'bi-thermometer-half'],
            'humidity' => ['label' => 'Kelembaban (Humidity)', 'unit' => '%', 'icon' => 'bi-droplet'],
            'rainfall' => ['label' => 'Curah Hujan (Rainfall)', 'unit' => 'mm', 'icon' => 'bi-cloud-rain'],
            'wind_speed' => ['label' => 'Kecepatan Angin', 'unit' => 'km/h', 'icon' => 'bi-wind'],
            'wind_direction' => ['label' => 'Arah Angin', 'unit' => '°', 'icon' => 'bi-compass'],
            'pressure' => ['label' => 'Tekanan Udara', 'unit' => 'hPa', 'icon' => 'bi-speedometer'],
            'uv_index' => ['label' => 'Indeks UV', 'unit' => '', 'icon' => 'bi-sun'],
            'light_intensity' => ['label' => 'Intensitas Cahaya', 'unit' => 'lux', 'icon' => 'bi-brightness-high'],
            'soil_moisture' => ['label' => 'Kelembaban Tanah', 'unit' => '%', 'icon' => 'bi-moisture'],
            'soil_ph' => ['label' => 'pH Tanah', 'unit' => '', 'icon' => 'bi-droplet-half'],
            'soil_temperature' => ['label' => 'Suhu Tanah', 'unit' => '°C', 'icon' => 'bi-thermometer'],
            'water_level' => ['label' => 'Level Air', 'unit' => 'cm', 'icon' => 'bi-water'],
            'co2' => ['label' => 'CO2', 'unit' => 'ppm', 'icon' => 'bi-cloud'],
            'ec' => ['label' => 'EC (Electrical Conductivity)', 'unit' => 'mS/cm', 'icon' => 'bi-lightning'],
            // Tambahkan sensor baru di sini:
            // 'pm25' => ['label' => 'PM2.5', 'unit' => 'µg/m³', 'icon' => 'bi-cloud-haze'],
        ];
    }

    /**
     * Sensor Default untuk Setiap Tipe Alat
     * Ini akan otomatis terpilih saat user memilih tipe alat
     * Format: ['sensor_key' => jumlah_default]
     */
    public static function getDefaultSensorsForType(string $type): array
    {
        $defaults = [
            'aws' => [
                'temperature' => 1,
                'humidity' => 1,
                'rainfall' => 1,
                'wind_speed' => 1,
                'pressure' => 1
            ],
            'smart_gh' => [
                'temperature' => 2,  // 2 sensor suhu
                'humidity' => 2,     // 2 sensor kelembaban
                'soil_moisture' => 1,
                'light_intensity' => 1,
            ],
            // Tambahkan default sensor untuk tipe baru:
            // 'water_quality' => ['water_level' => 1, 'ph' => 1, 'temperature' => 1],
        ];

        return $defaults[$type] ?? [];
    }

    /**
     * =====================================================
     * KONFIGURASI OUTPUT DEVICE
     * =====================================================
     */

    /**
     * Daftar Preset Output yang Tersedia
     * Format: 'output_key' => ['label' => 'Label', 'type' => 'tipe', 'unit' => 'satuan', 'icon' => 'bi-icon']
     * Tipe: boolean (on/off), number (angka), percentage (0-100%)
     */
    public static function getAvailableOutputs(): array
    {
        return [
            'relay' => ['label' => 'Relay', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-toggle-on'],
            'pump' => ['label' => 'Pompa Air', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-droplet-fill'],
            'fan' => ['label' => 'Kipas/Fan', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-fan'],
            'valve' => ['label' => 'Katup/Valve', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-moisture'],
            'motor' => ['label' => 'Motor Speed', 'type' => 'percentage', 'unit' => '%', 'icon' => 'bi-gear'],
            'led' => ['label' => 'LED', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-lightbulb'],
            'buzzer' => ['label' => 'Buzzer', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-bell'],
            'servo' => ['label' => 'Servo Motor', 'type' => 'number', 'unit' => '°', 'icon' => 'bi-arrow-repeat'],
            'heater' => ['label' => 'Pemanas/Heater', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-thermometer-high'],
            'sprinkler' => ['label' => 'Sprinkler', 'type' => 'boolean', 'unit' => '', 'icon' => 'bi-cloud-drizzle'],
            // Tambahkan output baru di sini
        ];
    }

    /**
     * Output Default untuk Setiap Tipe Alat
     * Format: ['output_key' => jumlah_default]
     */
    public static function getDefaultOutputsForType(string $type): array
    {
        $defaults = [
            'aws' => [
                // AWS biasanya tidak punya output, hanya monitoring
            ],
            'smart_gh' => [
                'pump' => 1,      // 1 pompa air
                'fan' => 1,       // 1 kipas
                'valve' => 1,     // 1 valve
                'led' => 1,       // 1 LED grow light
            ],
            // Tambahkan default output untuk tipe baru:
        ];

        return $defaults[$type] ?? [];
    }
}