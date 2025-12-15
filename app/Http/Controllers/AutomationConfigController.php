<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceOutput;
use App\Models\DeviceSensor;
use App\Models\OutputAutomationConfig;
use App\Models\UserDevice;
use App\Services\MqttAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AutomationConfigController extends Controller
{
    protected $mqttService;

    public function __construct(MqttAutomationService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Display automation configs for a specific device
     */
    public function index($deviceId)
    {
        // Check if user has access to this device
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $device = Device::with([
            'outputs.automationConfigs' => function ($query) use ($userDevice) {
                $query->where('user_device_id', $userDevice->id);
            },
            'outputs',
            'sensors'
        ])->findOrFail($deviceId);

        // Get all automation configs for this device
        $automations = OutputAutomationConfig::where('user_device_id', $userDevice->id)
            ->with(['deviceOutput'])
            ->get();

        $timeAutomations = $automations->where('automation_type', 'time');
        $sensorAutomations = $automations->where('automation_type', 'sensor');

        return view('automation.index', compact(
            'device',
            'userDevice',
            'timeAutomations',
            'sensorAutomations'
        ));
    }

    /**
     * Show form to create new automation
     */
    public function create($deviceId)
    {
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $device = Device::with(['outputs', 'sensors'])->findOrFail($deviceId);

        // Check limits
        $canAddTime = $device->canAddTimeSchedule();
        $canAddSensor = $device->canAddSensorAutomation();

        if (!$canAddTime && !$canAddSensor) {
            return redirect()->route('automation.index', $deviceId)
                ->with('error', 'Limit automasi untuk device ini sudah penuh!');
        }

        return view('automation.create', compact(
            'device',
            'userDevice',
            'canAddTime',
            'canAddSensor'
        ));
    }

    /**
     * Store new automation config
     */
    public function store(Request $request, $deviceId)
    {
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $device = Device::findOrFail($deviceId);

        // Validate request
        $validated = $request->validate([
            'automation_name' => 'required|string|max:255',
            'automation_type' => 'required|in:time,sensor',
            'device_output_id' => 'required|exists:device_outputs,id',
        ]);

        // Check limits
        if ($validated['automation_type'] === 'time' && !$device->canAddTimeSchedule()) {
            return back()->with('error', 'Limit time-based automation sudah penuh!')->withInput();
        }

        if ($validated['automation_type'] === 'sensor' && !$device->canAddSensorAutomation()) {
            return back()->with('error', 'Limit sensor-based automation sudah penuh!')->withInput();
        }

        // Type-specific validation and data preparation
        $data = [
            'user_device_id' => $userDevice->id,
            'device_output_id' => $validated['device_output_id'],
            'automation_name' => $validated['automation_name'],
            'automation_type' => $validated['automation_type'],
            'enabled' => true,
        ];

        if ($validated['automation_type'] === 'time') {
            $request->validate([
                'schedule_days' => 'required|array|min:1',
                'schedule_days.*' => 'integer|between:0,6',
                'schedule_time_start' => 'required|date_format:H:i',
                'schedule_time_end' => 'required|date_format:H:i',
                'schedule_value' => 'required|numeric',
            ]);

            $data['schedule_days'] = $request->schedule_days;
            $data['schedule_time_start'] = $request->schedule_time_start;
            $data['schedule_time_end'] = $request->schedule_time_end;
            $data['schedule_value'] = $request->schedule_value;

        } else { // sensor
            $request->validate([
                'sensor_conditions' => 'required|array|min:1|max:3',
                'sensor_conditions.*.sensor_id' => 'required|exists:device_sensors,id',
                'sensor_conditions.*.operator' => 'required|in:>,<,>=,<=,==',
                'sensor_conditions.*.threshold' => 'required|numeric',
                'sensor_conditions.*.hysteresis' => 'nullable|numeric|min:0',
                'condition_logic' => 'required|in:AND,OR',
                'action_value' => 'required|numeric',
            ]);

            $data['sensor_conditions'] = $request->sensor_conditions;
            $data['condition_logic'] = $request->condition_logic;
            $data['action_value'] = $request->action_value;
        }

        // Create automation config
        $automation = OutputAutomationConfig::create($data);

        // Send to device via MQTT
        $deviceToken = $device->token;
        $allConfigs = OutputAutomationConfig::where('user_device_id', $userDevice->id)
            ->with('deviceOutput')
            ->get();

        $this->mqttService->sendAutomationConfig($device->mqtt_topic, $deviceToken, $allConfigs->toArray());

        return redirect()->route('automation.index', $deviceId)
            ->with('success', 'Automation berhasil ditambahkan dan dikirim ke device!');
    }

    /**
     * Show form to edit automation
     */
    public function edit($id)
    {
        $automation = OutputAutomationConfig::with(['userDevice.device', 'deviceOutput'])
            ->findOrFail($id);

        // Check ownership
        if ($automation->userDevice->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $device = $automation->userDevice->device;
        $device->load(['outputs', 'sensors']);

        return view('automation.edit', compact('automation', 'device'));
    }

    /**
     * Update automation config
     */
    public function update(Request $request, $id)
    {
        $automation = OutputAutomationConfig::with(['userDevice.device'])->findOrFail($id);

        // Check ownership
        if ($automation->userDevice->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Validate
        $validated = $request->validate([
            'automation_name' => 'required|string|max:255',
        ]);

        $data = ['automation_name' => $validated['automation_name']];

        if ($automation->automation_type === 'time') {
            $request->validate([
                'schedule_days' => 'required|array|min:1',
                'schedule_days.*' => 'integer|between:0,6',
                'schedule_time_start' => 'required|date_format:H:i',
                'schedule_time_end' => 'required|date_format:H:i',
                'schedule_value' => 'required|numeric',
            ]);

            $data['schedule_days'] = $request->schedule_days;
            $data['schedule_time_start'] = $request->schedule_time_start;
            $data['schedule_time_end'] = $request->schedule_time_end;
            $data['schedule_value'] = $request->schedule_value;

        } else {
            $request->validate([
                'sensor_conditions' => 'required|array|min:1|max:3',
                'sensor_conditions.*.sensor_id' => 'required|exists:device_sensors,id',
                'sensor_conditions.*.operator' => 'required|in:>,<,>=,<=,==',
                'sensor_conditions.*.threshold' => 'required|numeric',
                'sensor_conditions.*.hysteresis' => 'nullable|numeric|min:0',
                'condition_logic' => 'required|in:AND,OR',
                'action_value' => 'required|numeric',
            ]);

            $data['sensor_conditions'] = $request->sensor_conditions;
            $data['condition_logic'] = $request->condition_logic;
            $data['action_value'] = $request->action_value;
        }

        $automation->update($data);

        // Send updated config to device
        $device = $automation->userDevice->device;
        $allConfigs = OutputAutomationConfig::where('user_device_id', $automation->user_device_id)
            ->with('deviceOutput')
            ->get();

        $this->mqttService->sendAutomationConfig($device->mqtt_topic, $device->token, $allConfigs->toArray());

        return redirect()->route('automation.index', $device->id)
            ->with('success', 'Automation berhasil diupdate!');
    }

    /**
     * Delete automation config
     */
    public function destroy($id)
    {
        $automation = OutputAutomationConfig::with(['userDevice.device'])->findOrFail($id);

        // Check ownership
        if ($automation->userDevice->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $device = $automation->userDevice->device;
        $deviceId = $device->id;
        $configId = $automation->id;

        // Delete from database
        $automation->delete();

        // Send delete command to device
        $this->mqttService->deleteAutomationConfig($device->mqtt_topic, $device->token, $configId);

        return redirect()->route('automation.index', $deviceId)
            ->with('success', 'Automation berhasil dihapus!');
    }

    /**
     * Toggle automation enabled status
     */
    public function toggle(Request $request, $id)
    {
        $automation = OutputAutomationConfig::with(['userDevice.device'])->findOrFail($id);

        // Check ownership
        if ($automation->userDevice->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $automation->enabled = !$automation->enabled;
        $automation->save();

        // Send updated config to device
        $device = $automation->userDevice->device;
        $allConfigs = OutputAutomationConfig::where('user_device_id', $automation->user_device_id)
            ->with('deviceOutput')
            ->get();

        $this->mqttService->sendAutomationConfig($device->mqtt_topic, $device->token, $allConfigs->toArray());

        return response()->json([
            'success' => true,
            'enabled' => $automation->enabled,
            'message' => 'Status automation berhasil diubah!',
        ]);
    }

    /**
     * Get sensors for device (API endpoint)
     */
    public function getSensorsForDevice($deviceId)
    {
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $sensors = DeviceSensor::where('device_id', $deviceId)
            ->select('id', 'sensor_name', 'sensor_label', 'unit')
            ->get();

        return response()->json($sensors);
    }

    /**
     * Sync all automations to device (re-send)
     */
    public function syncToDevice($deviceId)
    {
        $userDevice = UserDevice::where('user_id', Auth::id())
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $device = Device::findOrFail($deviceId);

        $allConfigs = OutputAutomationConfig::where('user_device_id', $userDevice->id)
            ->with('deviceOutput')
            ->get();

        $result = $this->mqttService->sendAutomationConfig($device->mqtt_topic, $device->token, $allConfigs->toArray());

        if ($result) {
            return back()->with('success', 'Semua automation berhasil di-sync ke device!');
        } else {
            return back()->with('error', 'Gagal sync automation ke device!');
        }
    }
}
