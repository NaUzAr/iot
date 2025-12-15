<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutputAutomationConfig extends Model
{
    protected $fillable = [
        'user_device_id',
        'device_output_id',
        'automation_name',
        'automation_type',
        'enabled',
        'schedule_days',
        'schedule_time_start',
        'schedule_time_end',
        'schedule_value',
        'sensor_conditions',
        'condition_logic',
        'action_value',
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'sensor_conditions' => 'array',
        'enabled' => 'boolean',
        'schedule_value' => 'float',
        'action_value' => 'float',
    ];

    // Relationships
    public function userDevice()
    {
        return $this->belongsTo(UserDevice::class);
    }

    public function deviceOutput()
    {
        return $this->belongsTo(DeviceOutput::class);
    }

    // Scopes
    public function scopeTimeType($query)
    {
        return $query->where('automation_type', 'time');
    }

    public function scopeSensorType($query)
    {
        return $query->where('automation_type', 'sensor');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    // Methods
    public function toMqttPayload()
    {
        $payload = [
            'id' => $this->id,
            'name' => $this->automation_name,
            'output_name' => $this->deviceOutput->output_name,
            'automation_type' => $this->automation_type,
            'enabled' => $this->enabled,
        ];

        if ($this->automation_type === 'time') {
            $payload['schedule'] = [
                'days' => $this->schedule_days,
                'start_time' => $this->schedule_time_start,
                'end_time' => $this->schedule_time_end,
                'value' => $this->schedule_value,
            ];
        } else {
            $payload['conditions'] = [
                'logic' => $this->condition_logic,
                'sensors' => $this->sensor_conditions,
                'action_value' => $this->action_value,
            ];
        }

        return $payload;
    }
}
