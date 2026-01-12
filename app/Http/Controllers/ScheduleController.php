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
     * Send single time schedule to device
     * Supports modes: time, time_days, time_days_sector
     */
    public function storeTimeSchedules(Request $request, $userDeviceId, $outputId)
    {
        $userDevice = UserDevice::where('id', $userDeviceId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $device = $userDevice->device;
        $output = DeviceOutput::findOrFail($outputId);

        $validated = $request->validate([
            'slot_id' => 'required|integer|min:1',
            'on_time' => 'required|date_format:H:i',
            'off_time' => 'required|date_format:H:i',
            'days' => 'nullable|string|max:7',
            'sector' => 'nullable|integer|min:1',
        ]);

        $schedule = [
            'id' => $validated['slot_id'],
            'on' => $validated['on_time'],
            'off' => $validated['off_time'],
        ];

        // Add days if provided (for time_days and time_days_sector modes)
        if (!empty($validated['days'])) {
            $schedule['days'] = $validated['days'];
        }

        // Add sector if provided (for time_days_sector mode)
        if (!empty($validated['sector'])) {
            $schedule['sector'] = $validated['sector'];
        }

        $success = $this->mqttService->sendSingleTimeSchedule(
            $device->mqtt_topic,
            $device->token,
            $output->output_name,
            $schedule,
            $output->automation_mode
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal slot ' . $validated['slot_id'] . ' berhasil dikirim ke device!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim jadwal ke device.',
        ], 500);
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
        ]);

        $rule = [
            'operator' => $validated['operator'],
            'threshold' => (float) $validated['threshold'],
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
