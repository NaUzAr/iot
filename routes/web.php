<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDeviceController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AutomationConfigController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MqttTesterController;


Route::middleware(['auth'])->group(function () {

    // Grouping khusus URL awalan /admin
    Route::prefix('admin')->name('admin.')->group(function () {

        // List Semua Device
        Route::get('/devices', [AdminDeviceController::class, 'index'])->name('devices.index');

        // Create Device (Yg sudah dibuat sebelumnya)
        Route::get('/create-device', [AdminDeviceController::class, 'create'])->name('device.create');
        Route::post('/create-device', [AdminDeviceController::class, 'store'])->name('device.store');

        // Edit Device
        Route::get('/device/{id}/edit', [AdminDeviceController::class, 'edit'])->name('device.edit');
        Route::put('/device/{id}', [AdminDeviceController::class, 'update'])->name('device.update');

        // Delete Device
        Route::delete('/device/{id}', [AdminDeviceController::class, 'destroy'])->name('device.destroy');

        // Monitoring Device (Admin View)
        Route::get('/device/{id}/monitoring', [AdminDeviceController::class, 'showMonitoring'])->name('device.monitoring');

        // MQTT Tester
        Route::get('/mqtt-tester', [MqttTesterController::class, 'index'])->name('mqtt-tester.index');
        Route::get('/mqtt-tester/device/{id}', [MqttTesterController::class, 'getDeviceDetails'])->name('mqtt-tester.device');
        Route::post('/mqtt-tester/send-sensor', [MqttTesterController::class, 'sendSensorData'])->name('mqtt-tester.send-sensor');
        Route::post('/mqtt-tester/send-output', [MqttTesterController::class, 'sendOutputControl'])->name('mqtt-tester.send-output');
        Route::post('/mqtt-tester/send-schedule', [MqttTesterController::class, 'sendSchedule'])->name('mqtt-tester.send-schedule');
        Route::post('/mqtt-tester/request-status', [MqttTesterController::class, 'requestStatus'])->name('mqtt-tester.request-status');
        Route::post('/mqtt-tester/send-custom', [MqttTesterController::class, 'sendCustom'])->name('mqtt-tester.send-custom');
    });

    // === MONITORING ROUTES (untuk semua user yang login) ===
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');
        Route::get('/add', [MonitoringController::class, 'create'])->name('create');
        Route::post('/add', [MonitoringController::class, 'store'])->name('store');
        Route::get('/device/{id}', [MonitoringController::class, 'show'])->name('show');
        Route::delete('/device/{id}', [MonitoringController::class, 'destroy'])->name('destroy');
        Route::post('/device/{id}/export', [MonitoringController::class, 'exportCsv'])->name('export');
        Route::post('/device/{id}/output/{outputId}/toggle', [MonitoringController::class, 'toggleOutput'])->name('output.toggle');
    });

    // === AUTOMATION ROUTES (untuk user kelola automation) ===
    Route::prefix('device/{deviceId}/automation')->name('automation.')->group(function () {
        Route::get('/', [AutomationConfigController::class, 'index'])->name('index');
        Route::get('/create', [AutomationConfigController::class, 'create'])->name('create');
        Route::post('/', [AutomationConfigController::class, 'store'])->name('store');
    });

    Route::prefix('automation')->name('automation.')->group(function () {
        Route::get('/{id}/edit', [AutomationConfigController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AutomationConfigController::class, 'update'])->name('update');
        Route::delete('/{id}', [AutomationConfigController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle', [AutomationConfigController::class, 'toggle'])->name('toggle');
        Route::get('/device/{deviceId}/sensors', [AutomationConfigController::class, 'getSensorsForDevice'])->name('sensors');
    });

    // === SCHEDULE MANAGEMENT ROUTES (Real-time MQTT) ===
    Route::prefix('device/{userDeviceId}/output/{outputId}/schedule')->name('schedule.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/time', [ScheduleController::class, 'storeTimeSchedules'])->name('time.store');
        Route::post('/sensor', [ScheduleController::class, 'storeSensorRule'])->name('sensor.store');
    });
    // Documentation (admin only for now, can adjust middleware later)
    Route::get('/documentation/mqtt', [DocumentationController::class, 'mqtt'])->name('documentation.mqtt');
    Route::get('/documentation/esp32', [DocumentationController::class, 'esp32'])->name('documentation.esp32');

});

// Beranda (public)
Route::get('/', function () {
    return view('page.beranda');
})->name('home');

// --- LOGIN ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- REGISTER ---
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');
