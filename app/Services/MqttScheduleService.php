<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MqttScheduleService
{
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct()
    {
        $this->host = config('mqtt.host', env('MQTT_HOST', 'smartagri.web.id'));
        $this->port = config('mqtt.port', env('MQTT_PORT', 1883));
        $this->username = config('mqtt.username', env('MQTT_USERNAME'));
        $this->password = config('mqtt.password', env('MQTT_PASSWORD'));
    }

    /**
     * Send time-based schedules to device
     * 
     * @param string $mqttTopic MQTT topic dari device (dari Admin Panel)
     * @param string $deviceToken Token device untuk identifikasi
     * @param string $outputName Nama output yang dijadwalkan
     * @param array $schedules Array jadwal
     */
    public function sendTimeSchedules(string $mqttTopic, string $deviceToken, string $outputName, array $schedules): bool
    {
        try {
            $mqtt = $this->connect();
            // Gunakan mqtt_topic dari device + /control
            $topic = "{$mqttTopic}/control";

            $message = json_encode([
                'type' => 'time_schedule',
                'token' => $deviceToken,
                'output' => $outputName,
                'schedules' => $schedules,
                'timestamp' => now()->toIso8601String(),
            ]);

            $mqtt->publish($topic, $message, 1); // QoS 1
            $mqtt->disconnect();

            Log::info("Time schedules sent to device via {$topic}", [
                'token' => $deviceToken,
                'output' => $outputName,
                'schedules_count' => count($schedules),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send schedules via MQTT: " . $e->getMessage(), [
                'mqtt_topic' => $mqttTopic,
                'output' => $outputName,
            ]);
            return false;
        }
    }

    /**
     * Send single time-based schedule to device
     * Format: <jdw{id}#{output}#{on}#{off}#>
     * Contoh: <jdw1#pump_1#06:00#18:00#>
     * 
     * @param string $mqttTopic MQTT topic dari device (dari Admin Panel)
     * @param string $deviceToken Token device untuk identifikasi
     * @param string $outputName Nama output yang dijadwalkan
     * @param array $schedule Single jadwal (id, on, off)
     */
    public function sendSingleTimeSchedule(string $mqttTopic, string $deviceToken, string $outputName, array $schedule): bool
    {
        try {
            $mqtt = $this->connect();
            $topic = "{$mqttTopic}/control";

            // Format simple: <jdw{id}#{output}#{on}#{off}#>
            $message = sprintf(
                '<jdw%d#%s#%s#%s#>',
                $schedule['id'],
                $outputName,
                $schedule['on'],
                $schedule['off']
            );

            $mqtt->publish($topic, $message, 1); // QoS 1
            $mqtt->disconnect();

            Log::info("Time schedule sent to device via {$topic}", [
                'message' => $message,
                'output' => $outputName,
                'slot_id' => $schedule['id'],
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send schedule via MQTT: " . $e->getMessage(), [
                'mqtt_topic' => $mqttTopic,
                'output' => $outputName,
            ]);
            return false;
        }
    }

    /**
     * Send sensor-based rule to device
     * Format: <set,output,operator.threshold>
     * Contoh: <set,pump,>.30>
     * 
     * @param string $mqttTopic MQTT topic dari device (dari Admin Panel)
     * @param string $deviceToken Token device untuk identifikasi
     * @param string $outputName Nama output
     * @param array $rule Aturan sensor (operator, threshold)
     */
    public function sendSensorRule(string $mqttTopic, string $deviceToken, string $outputName, array $rule): bool
    {
        try {
            $mqtt = $this->connect();
            $topic = "{$mqttTopic}/control";

            // Format simple: <set,output,operator.threshold>
            $message = sprintf(
                '<set,%s,%s.%s>',
                $outputName,
                $rule['operator'],
                $rule['threshold']
            );

            $mqtt->publish($topic, $message, 1);
            $mqtt->disconnect();

            Log::info("Sensor rule sent to device via {$topic}", [
                'message' => $message,
                'output' => $outputName,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send sensor rule via MQTT: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Request current status from device
     * 
     * @param string $mqttTopic MQTT topic dari device
     * @param string $deviceToken Token device
     */
    public function requestStatus(string $mqttTopic, string $deviceToken): bool
    {
        try {
            $mqtt = $this->connect();
            $topic = "{$mqttTopic}/control";

            $message = json_encode([
                'type' => 'status_request',
                'token' => $deviceToken,
                'timestamp' => now()->toIso8601String(),
            ]);

            $mqtt->publish($topic, $message, 1);
            $mqtt->disconnect();

            Log::info("Status request sent to topic {$topic}");

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to request status via MQTT: " . $e->getMessage());
            return false;
        }
    }

    private function connect(): MqttClient
    {
        $connectionSettings = new ConnectionSettings();

        if ($this->username && $this->password) {
            $connectionSettings = $connectionSettings
                ->setUsername($this->username)
                ->setPassword($this->password);
        }

        $connectionSettings = $connectionSettings
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(10);

        $mqtt = new MqttClient($this->host, $this->port, 'laravel-schedule-' . uniqid());
        $mqtt->connect($connectionSettings, true);

        return $mqtt;
    }
}
