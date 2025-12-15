<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MqttAutomationService
{
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct()
    {
        $this->host = config('mqtt.host', env('MQTT_HOST', 'broker.hivemq.com'));
        $this->port = config('mqtt.port', env('MQTT_PORT', 1883));
        $this->username = config('mqtt.username', env('MQTT_USERNAME'));
        $this->password = config('mqtt.password', env('MQTT_PASSWORD'));
    }

    /**
     * Send automation config to device
     * 
     * @param string $mqttTopic MQTT topic dari device (dari Admin Panel)
     * @param string $deviceToken Token device untuk identifikasi
     * @param array $configs Array konfigurasi automation
     */
    public function sendAutomationConfig(string $mqttTopic, string $deviceToken, array $configs): bool
    {
        try {
            $mqtt = $this->connect();
            $topic = "{$mqttTopic}/control";

            $configPayloads = array_map(function ($config) {
                return is_object($config) ? $config->toMqttPayload() : $config;
            }, $configs);

            $message = json_encode([
                'type' => 'automation_config',
                'token' => $deviceToken,
                'configs' => $configPayloads,
                'timestamp' => now()->toIso8601String(),
            ]);

            $mqtt->publish($topic, $message, 1);
            $mqtt->disconnect();

            Log::info("Automation config sent to topic {$topic}", [
                'token' => $deviceToken,
                'configs_count' => count($configs),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send automation config via MQTT: " . $e->getMessage(), [
                'mqtt_topic' => $mqttTopic,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete automation config from device
     * 
     * @param string $mqttTopic MQTT topic dari device
     * @param string $deviceToken Token device
     * @param int $configId ID konfigurasi yang akan dihapus
     */
    public function deleteAutomationConfig(string $mqttTopic, string $deviceToken, int $configId): bool
    {
        try {
            $mqtt = $this->connect();
            $topic = "{$mqttTopic}/control";

            $message = json_encode([
                'type' => 'automation_delete',
                'token' => $deviceToken,
                'config_id' => $configId,
                'timestamp' => now()->toIso8601String(),
            ]);

            $mqtt->publish($topic, $message, 1);
            $mqtt->disconnect();

            Log::info("Automation delete command sent to topic {$topic}", [
                'config_id' => $configId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send delete command via MQTT: " . $e->getMessage(), [
                'mqtt_topic' => $mqttTopic,
                'config_id' => $configId,
            ]);
            return false;
        }
    }

    /**
     * Request device status
     * 
     * @param string $mqttTopic MQTT topic dari device
     * @param string $deviceToken Token device
     */
    public function requestDeviceStatus(string $mqttTopic, string $deviceToken): bool
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
            Log::error("Failed to request device status via MQTT: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send output control command to device
     * 
     * @param string $mqttTopic MQTT topic dari device
     * @param string $deviceToken Token device
     * @param string $outputName Nama output yang dikontrol
     * @param mixed $value Nilai output (true/false atau angka)
     */
    public function sendOutputControl(string $mqttTopic, string $deviceToken, string $outputName, $value): bool
    {
        try {
            $mqtt = $this->connect();
            $topic = "{$mqttTopic}/control";

            $message = json_encode([
                'type' => 'manual_control',
                'token' => $deviceToken,
                'output_name' => $outputName,
                'value' => $value,
                'timestamp' => now()->toIso8601String(),
            ]);

            $mqtt->publish($topic, $message, 1);
            $mqtt->disconnect();

            Log::info("Output control sent to topic {$topic}", [
                'output' => $outputName,
                'value' => $value,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send output control via MQTT: " . $e->getMessage());
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

        $mqtt = new MqttClient($this->host, $this->port, 'laravel-automation-' . uniqid());
        $mqtt->connect($connectionSettings, true);

        return $mqtt;
    }
}
