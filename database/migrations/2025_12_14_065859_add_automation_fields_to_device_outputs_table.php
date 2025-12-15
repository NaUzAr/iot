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
        Schema::table('device_outputs', function (Blueprint $table) {
            $table->enum('automation_mode', ['none', 'time', 'sensor'])->default('none')->after('current_value');
            $table->integer('max_schedules')->default(8)->nullable()->after('automation_mode');
            $table->foreignId('automation_sensor_id')->nullable()->constrained('device_sensors')->onDelete('set null')->after('max_schedules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_outputs', function (Blueprint $table) {
            $table->dropForeign(['automation_sensor_id']);
            $table->dropColumn(['automation_mode', 'max_schedules', 'automation_sensor_id']);
        });
    }
};
