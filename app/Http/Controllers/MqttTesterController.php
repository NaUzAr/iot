<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceSensor;
use App\Models\DeviceOutput;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MqttTesterController extends Controller
{
    /**
     * Show MQTT Tester page
     */
    public function index()
    {
        $devices = Device::with(['sensors', 'outputs'])->get();
        return view('admin.mqtt_tester', compact('devices'));
    }

    /**
     * Get device details (sensors, outputs) via AJAX
     */
    public function getDeviceDetails($id)
    {
        $device = Device::with(['sensors', 'outputs'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'token' => $device->token,
                'mqtt_topic' => $device->mqtt_topic,
                'type' => $device->type,
            ],
            'sensors' => $device->sensors->map(function ($sensor) {
                return [
                    'id' => $sensor->id,
                    'sensor_name' => $sensor->sensor_name,
                    'sensor_label' => $sensor->sensor_label,
                    'unit' => $sensor->unit,
                ];
            }),
            'outputs' => $device->outputs->map(function ($output) {
                return [
                    'id' => $output->id,
                    'output_name' => $output->output_name,
                    'output_label' => $output->output_label,
                    'output_type' => $output->output_type,
                    'unit' => $output->unit,
                ];
            }),
        ]);
    }

    /**
     * Send test sensor data to MQTT
     */
    public function sendSensorData(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'mqtt_topic' => 'required|string',
            'sensor_data' => 'required|array',
        ]);

        try {
            $mqtt = $this->connect();

            // Build message with token and sensor data
            $message = array_merge(
                ['token' => $request->token],
                $request->sensor_data
            );

            $mqtt->publish($request->mqtt_topic, json_encode($message), 1);
            $mqtt->disconnect();

            Log::info("MQTT Test: Sensor data sent to {$request->mqtt_topic}", $message);

            return response()->json([
                'success' => true,
                'message' => 'Data sensor berhasil dikirim ke MQTT!',
                'topic' => $request->mqtt_topic,
                'payload' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error("MQTT Test Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test output control command to MQTT
     */
    public function sendOutputControl(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'mqtt_topic' => 'required|string',
            'outputs' => 'required|array',
        ]);

        try {
            $mqtt = $this->connect();

            // Control topic is mqtt_topic/control
            $topic = $request->mqtt_topic . '/control';

            $message = [
                'token' => $request->token,
                'action' => 'set_output',
                'outputs' => $request->outputs,
                'timestamp' => now()->toIso8601String(),
            ];

            $mqtt->publish($topic, json_encode($message), 1);
            $mqtt->disconnect();

            Log::info("MQTT Test: Output control sent to {$topic}", $message);

            return response()->json([
                'success' => true,
                'message' => 'Perintah output berhasil dikirim!',
                'topic' => $topic,
                'payload' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error("MQTT Test Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test schedule to device
     */
    public function sendSchedule(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'mqtt_topic' => 'required|string',
            'output_name' => 'required|string',
            'schedule_type' => 'required|in:time,sensor',
            'schedule_data' => 'required|array',
        ]);

        try {
            $mqtt = $this->connect();
            $topic = $request->mqtt_topic . '/control';

            if ($request->schedule_type === 'time') {
                $message = [
                    'type' => 'time_schedule',
                    'token' => $request->token,
                    'output' => $request->output_name,
                    'schedules' => $request->schedule_data,
                    'timestamp' => now()->toIso8601String(),
                ];
            } else {
                $message = [
                    'type' => 'sensor_rule',
                    'token' => $request->token,
                    'output' => $request->output_name,
                    'rule' => $request->schedule_data,
                    'timestamp' => now()->toIso8601String(),
                ];
            }

            $mqtt->publish($topic, json_encode($message), 1);
            $mqtt->disconnect();

            Log::info("MQTT Test: Schedule sent to {$topic}", $message);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dikirim ke device!',
                'topic' => $topic,
                'payload' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error("MQTT Test Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send status request to device
     */
    public function requestStatus(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'mqtt_topic' => 'required|string',
        ]);

        try {
            $mqtt = $this->connect();
            $topic = $request->mqtt_topic . '/control';

            $message = [
                'type' => 'status_request',
                'token' => $request->token,
                'timestamp' => now()->toIso8601String(),
            ];

            $mqtt->publish($topic, json_encode($message), 1);
            $mqtt->disconnect();

            Log::info("MQTT Test: Status request sent to {$topic}");

            return response()->json([
                'success' => true,
                'message' => 'Request status berhasil dikirim!',
                'topic' => $topic,
                'payload' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error("MQTT Test Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send custom JSON payload to MQTT
     */
    public function sendCustom(Request $request)
    {
        $request->validate([
            'mqtt_topic' => 'required|string',
            'payload' => 'required|array',
        ]);

        try {
            $mqtt = $this->connect();

            $mqtt->publish($request->mqtt_topic, json_encode($request->payload), 1);
            $mqtt->disconnect();

            Log::info("MQTT Test: Custom payload sent to {$request->mqtt_topic}", $request->payload);

            return response()->json([
                'success' => true,
                'message' => 'Custom JSON berhasil dikirim ke MQTT!',
                'topic' => $request->mqtt_topic,
                'payload' => $request->payload,
            ]);

        } catch (\Exception $e) {
            Log::error("MQTT Test Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function connect(): MqttClient
    {
        $host = config('mqtt.host', env('MQTT_HOST', 'broker.hivemq.com'));
        $port = config('mqtt.port', env('MQTT_PORT', 1883));
        $username = config('mqtt.username', env('MQTT_USERNAME'));
        $password = config('mqtt.password', env('MQTT_PASSWORD'));

        $connectionSettings = new ConnectionSettings();

        if ($username && $password) {
            $connectionSettings = $connectionSettings
                ->setUsername($username)
                ->setPassword($password);
        }

        $connectionSettings = $connectionSettings
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(30)
            ->setSocketTimeout(30);

        $mqtt = new MqttClient($host, $port, 'laravel-tester-' . uniqid());
        $mqtt->connect($connectionSettings, true);

        return $mqtt;
    }
}
