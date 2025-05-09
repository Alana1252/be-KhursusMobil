<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JadwalSiswaController;
use App\Http\Controllers\Api\PaketController;
use App\Http\Controllers\Api\PesananController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔒 Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [AuthController::class, 'index'])->middleware('permission:lihat-user');
    Route::post('/users', [AuthController::class, 'store'])->middleware('permission:tambah-user');
    Route::get('/users/{user}', [AuthController::class, 'show'])->middleware('permission:lihat-user');
    Route::put('/users/{user}', [AuthController::class, 'update'])->middleware('permission:edit-user');
    Route::delete('/users/{user}', [AuthController::class, 'destroy'])->middleware('permission:hapus-user');

    Route::get('paket/{paket}', [PaketController::class, 'show'])->middleware('permission:lihat-paket');
    Route::post('paket', [PaketController::class, 'store'])->middleware('permission:tambah-paket');
    Route::get('paket', [PaketController::class, 'index'])->middleware('permission:lihat-paket');
    Route::put('paket/{paket}', [PaketController::class, 'update'])->middleware('permission:edit-paket');
    Route::delete('paket/{paket}', [PaketController::class, 'destroy'])->middleware('permission:hapus-paket');
  
    Route::get('jadwal-siswa', [JadwalSiswaController::class, 'index'])->middleware('permission:lihat-jadwal-siswa');
    Route::post('jadwal-siswa/tambah/{pesanan}', [JadwalSiswaController::class, 'tambahJadwal'])->middleware('permission:tambah-jadwal-siswa');
    Route::get('jadwal-siswa/{jadwalSiswa}', [JadwalSiswaController::class, 'show'])->middleware('permission:lihat-jadwal-siswa');  
    Route::put('jadwal-siswa/{jadwalSiswa}', [JadwalSiswaController::class, 'update'])->middleware('permission:edit-jadwal-siswa');
    Route::delete('jadwal-siswa/{jadwalSiswa}', [JadwalSiswaController::class, 'destroy'])->middleware('permission:hapus-jadwal-siswa');

    Route::get('pesanan', [PesananController::class, 'index'])->middleware('permission:lihat-pesanan');
    Route::post('pesanan', [PesananController::class, 'store'])->middleware('permission:tambah-pesanan');
    Route::get('pesanan/{pesanan}', [PesananController::class, 'show'])->middleware('permission:lihat-pesanan');
    Route::put('pesanan/{pesanan}', [PesananController::class, 'update'])->middleware('permission:edit-pesanan');
    Route::delete('pesanan/{pesanan}', [PesananController::class, 'destroy'])->middleware('permission:hapus-pesanan');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
