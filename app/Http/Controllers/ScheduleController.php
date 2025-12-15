<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\UserDevice;
use App\Models\DeviceOutput;
use App\Services\MqttScheduleService;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    private $mqttService;

    public function __construct(MqttScheduleService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Show schedule management page for specific output
     */
    public function index($userDeviceId, $outputId)
    {
        $userDevice = UserDevice::where('id', $userDeviceId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $device = $userDevice->device;

        $output = DeviceOutput::where('id', $outputId)
            ->where('device_id', $device->id)
            ->firstOrFail();

        // Check if this output has automation configured
        if ($output->automation_mode === 'none') {
            return redirect()->route('monitoring.show', $userDevice->id)
                ->with('error', 'Output ini tidak memiliki automation yang dikonfigurasi.');
        }

        return view('schedule.index', compact('userDevice', 'device', 'output'));
    }

    /**
     * Send time schedules to device
     */
    public function storeTimeSchedules(Request $request, $userDeviceId, $outputId)
    {
        $userDevice = UserDevice::where('id', $userDeviceId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $device = $userDevice->device;
        $output = DeviceOutput::findOrFail($outputId);

        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.on_time' => 'required|date_format:H:i',
            'schedules.*.off_time' => 'required|date_format:H:i',
        ]);

        $schedules = array_map(function ($schedule, $index) {
            return [
                'id' => $index + 1,
                'on' => $schedule['on_time'],
                'off' => $schedule['off_time'],
            ];
        }, $validated['schedules'], array_keys($validated['schedules']));

        $success = $this->mqttService->sendTimeSchedules(
            $device->mqtt_topic,
            $device->token,
            $output->output_name,
            $schedules
        );

        if ($success) {
            return back()->with('success', 'Schedule berhasil dikirim ke device!');
        }

        return back()->with('error', 'Gagal mengirim schedule ke device.');
    }

    /**
     * Send sensor rule to device
     */
    public function storeSensorRule(Request $request, $userDeviceId, $outputId)
    {
        $userDevice = UserDevice::where('id', $userDeviceId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $device = $userDevice->device;
        $output = DeviceOutput::findOrFail($outputId);

        $validated = $request->validate([
            'operator' => 'required|in:>,<,>=,<=,==',
            'threshold' => 'required|numeric',
            'hysteresis' => 'nullable|numeric',
            'action_on' => 'required|numeric',
            'action_off' => 'required|numeric',
        ]);

        $rule = [
            'sensor' => $output->automationSensor->sensor_name,
            'operator' => $validated['operator'],
            'threshold' => (float) $validated['threshold'],
            'hysteresis' => (float) ($validated['hysteresis'] ?? 2),
            'action_on' => (float) $validated['action_on'],
            'action_off' => (float) $validated['action_off'],
        ];

        $success = $this->mqttService->sendSensorRule(
            $device->mqtt_topic,
            $device->token,
            $output->output_name,
            $rule
        );

        if ($success) {
            return back()->with('success', 'Sensor rule berhasil dikirim ke device!');
        }

        return back()->with('error', 'Gagal mengirim sensor rule ke device.');
    }
}
