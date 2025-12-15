<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('output_automation_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_device_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_output_id')->constrained()->onDelete('cascade');
            $table->string('automation_name');
            $table->enum('automation_type', ['time', 'sensor']);
            $table->boolean('enabled')->default(true);

            // Time-based fields
            $table->json('schedule_days')->nullable();      // [0-6] where 0=Sunday
            $table->time('schedule_time_start')->nullable();
            $table->time('schedule_time_end')->nullable();
            $table->float('schedule_value')->nullable();

            // Sensor-based fields
            $table->json('sensor_conditions')->nullable();   // Array of sensor conditions
            $table->enum('condition_logic', ['AND', 'OR'])->nullable();
            $table->float('action_value')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('output_automation_configs');
    }
};
