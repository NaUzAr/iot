<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// --- IMPORT PENTING UNTUK DATABASE ---
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// -------------------------------------

class AdminDeviceController extends Controller
{
    // Helper: Pastikan yang akses adalah Admin
    private function checkAdmin()
    {
        // Pastikan kolom 'role' sudah ada di tabel users
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Admin.');
        }
    }

    // 1. HALAMAN LIST DEVICE (INDEX)
    public function index()
    {
        $this->checkAdmin();
        $devices = Device::all();
        return view('admin.index', compact('devices'));
    }

    // 2. HALAMAN FORM CREATE
    public function create()
    {
        $this->checkAdmin();
        return view('admin.create_device');
    }

    // 3. PROSES SIMPAN DEVICE BARU (STORE)
    public function store(Request $request)
    {
        $this->checkAdmin();

        // A. Validasi Input
        $request->validate([
            'name' => 'required|string|max:100',
            'mqtt_topic' => 'required|string|max:100',
        ]);

        // B. Generate Token Unik & Nama Tabel
        $token = Str::random(16); // Token acak 16 karakter
        $tableName = 'log_' . $token; // Nama tabel: log_x8s7d...

        // C. BUAT TABEL LOG OTOMATIS (Schema Builder)
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                // Sesuaikan kolom ini dengan sensor Weather Station kamu
                $table->float('temperature')->nullable();
                $table->float('humidity')->nullable();
                $table->float('rainfall')->nullable();
                $table->timestamp('recorded_at')->useCurrent();
            });
        }

        // D. Simpan Metadata ke Tabel Devices
        Device::create([
            'name' => $request->name,
            'mqtt_topic' => $request->mqtt_topic,
            'token' => $token,
            'table_name' => $tableName,
        ]);

        // E. Redirect ke Halaman List Device (Bukan Dashboard)
        return redirect()->route('admin.devices.index')
            ->with('success', "Sukses! Device '$request->name' berhasil dibuat dan Tabel Log siap.");
    }

    // 4. HALAMAN FORM EDIT
    public function edit($id)
    {
        $this->checkAdmin();
        $device = Device::findOrFail($id);
        return view('admin.edit', compact('device'));
    }

    // 5. PROSES UPDATE DEVICE
    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
            'mqtt_topic' => 'required|string|max:100',
        ]);

        $device = Device::findOrFail($id);

        $device->update([
            'name' => $request->name,
            'mqtt_topic' => $request->mqtt_topic,
            // Token & table_name JANGAN diupdate agar koneksi database aman
        ]);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Data device berhasil diperbarui!');
    }

    // 6. PROSES HAPUS DEVICE (DESTROY)
    public function destroy($id)
    {
        $this->checkAdmin();
        $device = Device::findOrFail($id);

        // A. Hapus Tabel Log fisiknya dari database (PENTING!)
        // Hati-hati, data sensor akan hilang permanen
        Schema::dropIfExists($device->table_name);

        // B. Hapus data dari tabel devices
        $device->delete();

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device dan Tabel Log berhasil dihapus permanen.');
    }

    // 7. HALAMAN MONITORING DEVICE (ADMIN VIEW)
    public function showMonitoring($id)
    {
        $this->checkAdmin();

        $device = Device::with(['sensors', 'outputs'])->findOrFail($id);
        $sensors = $device->sensors;
        $outputs = $device->outputs;

        // Default values
        $logData = collect();
        $chartData = collect();
        $latestData = null;

        if ($device->table_name && Schema::hasTable($device->table_name)) {
            // Ambil 50 data terbaru untuk chart
            $chartData = \DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            // Ambil data untuk tabel dengan pagination (20 per halaman)
            $logData = \DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->paginate(20);

            // Ambil data terbaru untuk display sensor cards
            $latestData = \DB::table($device->table_name)
                ->orderBy('recorded_at', 'desc')
                ->first();
        } else {
            // Buat paginator kosong jika tidak ada data
            $logData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('admin.monitoring', compact('device', 'sensors', 'outputs', 'logData', 'chartData', 'latestData'));
    }
}