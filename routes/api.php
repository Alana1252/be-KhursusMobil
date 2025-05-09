<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Kasir\{
    JadwalSiswaController,
    PesananController,
    PaketController
};
use App\Http\Controllers\Api\Siswa\SiswaController;
use App\Http\Controllers\Api\Instruktur\InstrukturController;
use App\Http\Controllers\Api\Owner\OwnerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "api" middleware group.
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 👤 User Management
    Route::prefix('users')->middleware('permission:lihat-user')->group(function () {
        Route::get('/', [AuthController::class, 'index']);
        Route::get('/{user}', [AuthController::class, 'show']);
    });
    Route::post('/users', [AuthController::class, 'store'])->middleware('permission:tambah-user');
    Route::put('/users/{user}', [AuthController::class, 'update'])->middleware('permission:edit-user');
    Route::delete('/users/{user}', [AuthController::class, 'destroy'])->middleware('permission:hapus-user');

    // 📦 Paket
    Route::prefix('paket')->group(function () {
        Route::get('/', [PaketController::class, 'index'])->middleware('permission:lihat-paket');
        Route::post('/', [PaketController::class, 'store'])->middleware('permission:tambah-paket');
        Route::get('/{paket}', [PaketController::class, 'show'])->middleware('permission:lihat-paket');
        Route::put('/{paket}', [PaketController::class, 'update'])->middleware('permission:edit-paket');
        Route::delete('/{paket}', [PaketController::class, 'destroy'])->middleware('permission:hapus-paket');
    });

    // 📅 Jadwal Siswa
    Route::prefix('jadwal-siswa')->group(function () {
        Route::get('/', [JadwalSiswaController::class, 'index'])->middleware('permission:lihat-jadwal-siswa');
        Route::post('/{pesanan}/tambah', [JadwalSiswaController::class, 'tambahJadwal'])->middleware('permission:tambah-jadwal-siswa');
        Route::get('/{jadwalSiswa}', [JadwalSiswaController::class, 'show'])->middleware('permission:lihat-jadwal-siswa');
        Route::put('/{jadwalSiswa}/status', [JadwalSiswaController::class, 'completedJadwal'])->middleware('permission:edit-jadwal-siswa');
        Route::delete('/{jadwalSiswa}', [JadwalSiswaController::class, 'destroy'])->middleware('permission:hapus-jadwal-siswa');
    });

    // 🧾 Pesanan
    Route::prefix('pesanan')->group(function () {
        Route::get('/', [PesananController::class, 'index'])->middleware('permission:lihat-pesanan');
        Route::post('/', [PesananController::class, 'store'])->middleware('permission:tambah-pesanan');
        Route::get('/{pesanan}', [PesananController::class, 'show'])->middleware('permission:lihat-pesanan');
        Route::put('/{pesanan}', [PesananController::class, 'update'])->middleware('permission:edit-pesanan');
        Route::delete('/{pesanan}', [PesananController::class, 'destroy'])->middleware('permission:hapus-pesanan');
        Route::post('/{pesanan}/bukti', [PesananController::class, 'uploadBukti'])->middleware('permission:tambah-pesanan');
    });

    // 🎓 Siswa
    Route::prefix('siswa')->group(function () {
        Route::post('/add-jadwal/{pesanan}', [SiswaController::class, 'tambahJadwal'])->middleware('permission:tambah-jadwal-siswa');
        Route::get('/lihat-pesanan', [SiswaController::class, 'lihatPesanan'])->middleware('permission:lihat-pesanan');
    });

    // 👨‍🏫 Instruktur
    Route::get('/instruktur/all-user', [InstrukturController::class, 'semuaMuridWithPesananAndPendingJadwal'])->middleware('permission:lihat-semua-pending-jadwal');
    Route::put('/instruktur/update-jadwal-status/{jadwal}', [InstrukturController::class, 'updateJadwalStatus'])->middleware('permission:edit-jadwal-siswa');

    // 👨‍💻 Owner
    Route::get('/owner/data', [OwnerController::class, 'lihatSemuaDetail'])->middleware('permission:lihat-semua-detail');

    
});
