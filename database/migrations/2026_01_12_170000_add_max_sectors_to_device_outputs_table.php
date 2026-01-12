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
            // Tambah kolom max_sectors untuk mode time_days_sector
            $table->integer('max_sectors')->default(1)->after('max_schedules');
        });

        // Update enum automation_mode untuk mendukung mode baru
        // Karena Laravel tidak support alter enum dengan baik, kita drop dan recreate
        // Tapi ini berbahaya untuk data existing, jadi kita pakai string saja
        // Mode: none, time, time_days, time_days_sector, sensor
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_outputs', function (Blueprint $table) {
            $table->dropColumn('max_sectors');
        });
    }
};
