<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MqttListener extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mqtt:listen 
                            {--host=localhost : MQTT Broker Host}
                            {--port=1883 : MQTT Broker Port}
                            {--username= : MQTT Username (optional)}
                            {--password= : MQTT Password (optional)}';

    /**
     * The console command description.
     */
    protected $description = 'Listen to MQTT broker for sensor data and device status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');
        $username = $this->option('username');
        $password = $this->option('password');

        $this->info("🚀 Starting MQTT Listener...");
        $this->info("   Broker: {$host}:{$port}");

        try {
            // Setup connection
            $connectionSettings = new ConnectionSettings();

            if ($username && $password) {
                $connectionSettings = $connectionSettings
                    ->setUsername($username)
                    ->setPassword($password);
            }

            $connectionSettings = $connectionSettings
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(10);

            // Create MQTT client
            $mqtt = new MqttClient($host, $port, 'laravel-listener-' . uniqid());
            $mqtt->connect($connectionSettings, true);

            $this->info("✅ Connected to MQTT Broker!");

            // Get all devices and subscribe to their topics
            $devices = Device::all();

            if ($devices->isEmpty()) {
                $this->warn("⚠️  No devices found. Create devices first via admin panel.");
                $this->info("   Listening for wildcard topic: sensor/#");

                $mqtt->subscribe('sensor/#', function ($topic, $message) {
                    $this->processMessage($topic, $message);
                }, 0);
            } else {
                foreach ($devices as $device) {
                    $topic = $device->mqtt_topic;
                    $this->info("📡 Subscribed to: {$topic}");

                    $mqtt->subscribe($topic, function ($topic, $message) {
                        $this->processMessage($topic, $message);
                    }, 0);
                }

                // Also subscribe to wildcard for new devices
                $mqtt->subscribe('sensor/#', function ($topic, $message) {
                    $this->processMessage($topic, $message);
                }, 0);
            }

            // Subscribe to device status topic (device-as-master)
            $this->info("📡 Subscribed to: devices/+/status (Device Status)");
            $mqtt->subscribe('devices/+/status', function ($topic, $message) {
                $this->processDeviceStatus($topic, $message);
            }, 1);

            $this->info("");
            $this->info("👂 Listening for messages... (Press Ctrl+C to stop)");
            $this->info("─────────────────────────────────────────────────");

            // Loop forever
            $mqtt->loop(true);

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error('MQTT Listener Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Process incoming MQTT message (sensor data)
     */
    private function processMessage($topic, $message)
    {
        $timestamp = now()->format('H:i:s');
        $this->line("[{$timestamp}] 📨 Topic: {$topic}");
        $this->line("           Data: {$message}");

        try {
            // Parse JSON message
            $data = json_decode($message, true);

            if (!$data) {
                $this->warn("           ⚠️  Invalid JSON format");
                return;
            }

            // Cari device berdasarkan topic ATAU token
            $device = null;

            // Coba cari by MQTT topic
            $device = Device::where('mqtt_topic', $topic)->first();

            // Kalau tidak ketemu, coba cari by token di data
            if (!$device && isset($data['token'])) {
                $device = Device::where('token', $data['token'])->first();
            }

            if (!$device) {
                $this->warn("           ⚠️  Device not found for topic: {$topic}");
                return;
            }

            // Prepare data untuk insert
            $tableName = $device->table_name;

            if (!\Schema::hasTable($tableName)) {
                $this->warn("           ⚠️  Table {$tableName} does not exist");
                return;
            }

            // Get sensor columns from device
            $sensors = $device->sensors;
            $insertData = ['recorded_at' => now()];

            foreach ($sensors as $sensor) {
                $sensorName = $sensor->sensor_name;
                if (isset($data[$sensorName])) {
                    $insertData[$sensorName] = (float) $data[$sensorName];
                }
            }

            // Insert to database
            DB::table($tableName)->insert($insertData);

            $this->info("           ✅ Data saved to {$tableName}");

        } catch (\Exception $e) {
            $this->error("           ❌ Error: " . $e->getMessage());
            Log::error('MQTT Process Error: ' . $e->getMessage());
        }
    }

    /**
     * Process device status message (device-as-master architecture)
     * Device sends its output states and schedules, web only displays
     */
    private function processDeviceStatus($topic, $message)
    {
        $timestamp = now()->format('H:i:s');
        $this->line("");
        $this->line("[{$timestamp}] 🔔 Device Status Received!");
        $this->line("           Topic: {$topic}");

        try {
            $data = json_decode($message, true);

            if (!$data) {
                $this->warn("           ⚠️  Invalid JSON format");
                return;
            }

            $token = $data['token'] ?? 'unknown';

            // Find device by token
            $device = Device::where('token', $token)->first();
            $deviceName = $device ? $device->name : "Unknown Device";

            $this->info("           📱 Device: {$deviceName} ({$token})");

            // Display outputs
            if (isset($data['outputs']) && is_array($data['outputs'])) {
                $this->line("           📊 Output States:");
                foreach ($data['outputs'] as $name => $output) {
                    $value = is_array($output) ? ($output['value'] ?? 0) : $output;
                    $label = is_array($output) ? ($output['label'] ?? $name) : $name;
                    $status = $value ? "ON 🟢" : "OFF 🔴";
                    $this->line("              • {$label}: {$status}");
                }
            }

            // Display sensor values
            if (isset($data['sensors']) && is_array($data['sensors'])) {
                $this->line("           🌡️ Sensor Values:");
                foreach ($data['sensors'] as $name => $value) {
                    $this->line("              • {$name}: {$value}");
                }
            }

            // Display schedules count
            if (isset($data['schedules']) && is_array($data['schedules'])) {
                $count = count($data['schedules']);
                $enabled = count(array_filter($data['schedules'], fn($s) => $s['enabled'] ?? true));
                $this->line("           📅 Schedules: {$count} total, {$enabled} enabled");
            }

            $this->info("           ✅ Status received (not saved - device is master)");

        } catch (\Exception $e) {
            $this->error("           ❌ Error: " . $e->getMessage());
            Log::error('MQTT Device Status Error: ' . $e->getMessage());
        }
    }
}
