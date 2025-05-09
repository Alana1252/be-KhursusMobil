<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{

    public function tambahJadwal(Pesanan $pesanan, Request $request)
{
    $user = Auth::user();

    // Cek apakah pesanan milik user yang sedang login
    if ($pesanan->user_id !== $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki akses ke pesanan ini.'
        ], 403);
    }

    // Cek apakah bukti pembayaran sudah diupload
    if ($pesanan->bukti_pembayaran === null) {
        return response()->json([
            'success' => false,
            'message' => 'Harap upload bukti pembayaran terlebih dahulu.'
        ], 403);
    }

    // Validasi input
    $validator = Validator::make($request->all(), [
        'jadwal' => ['required', 'array', 'min:1'],
        'jadwal.*.tanggal' => ['required', 'date'],
        'jadwal.*.waktu_mulai' => ['required', 'date_format:H:i'],
        'jadwal.*.waktu_selesai' => ['required', 'date_format:H:i', 'after:jadwal.*.waktu_mulai'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()
        ], 422);
    }

    // Hitung total jam yang sudah dipakai
    $jamTerpakai = $pesanan->jadwal
        ->whereIn('status', ['pending', 'finished'])
        ->reduce(function ($carry, $jadwal) {
            $mulai = Carbon::parse($jadwal->waktu_mulai);
            $selesai = Carbon::parse($jadwal->waktu_selesai);
            return $carry + $selesai->diffInMinutes($mulai) / 60;
        }, 0);

    // Hitung total jam yang ingin ditambahkan
    $jamBaru = 0;
    foreach ($request->jadwal as $jadwalData) {
        $mulai = Carbon::parse($jadwalData['waktu_mulai']);
        $selesai = Carbon::parse($jadwalData['waktu_selesai']);
        $jamBaru += $selesai->diffInMinutes($mulai) / 60;
    }

    // Total jam tidak boleh melebihi paket
    $totalJamPaket = (float) $pesanan->paket->jumlah_jam;
    if (($jamTerpakai + $jamBaru) > $totalJamPaket) {
        return response()->json([
            'success' => false,
            'message' => 'Total jam melebihi kuota dari paket.',
            'sisa_jam' => $totalJamPaket - $jamTerpakai
        ], 400);
    }

    // Simpan semua jadwal
    foreach ($request->jadwal as $jadwalData) {
        Jadwal::create([
            'pesanan_id' => $pesanan->id,
            'tanggal' => $jadwalData['tanggal'],
            'waktu_mulai' => $jadwalData['waktu_mulai'],
            'waktu_selesai' => $jadwalData['waktu_selesai'],
            'status' => 'pending'
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Jadwal berhasil ditambahkan.'
    ], 200);
}

public function lihatPesanan()
{
    $user = Auth::user();
    
    // Ambil pesanan milik user beserta relasi
    $pesanan = Pesanan::with(['paket', 'jadwal'])->where('user_id', $user->id)->first();

    if (!$pesanan) {
        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak ditemukan.'
        ], 404);
    }

    // Total jam paket
    $totalJamPaket = (float) $pesanan->paket->jumlah_jam;

    // Inisialisasi variabel
    $totalJamTerpakai = 0;
    $jadwalDetail = [];

    foreach ($pesanan->jadwal as $jadwal) {
        $mulai = Carbon::parse($jadwal->waktu_mulai);
        $selesai = $jadwal->waktu_selesai ? Carbon::parse($jadwal->waktu_selesai) : null;

        // Hitung durasi setiap jadwal (jika waktu_selesai ada)
        $durasiJam = $selesai ? $selesai->diffInMinutes($mulai) / 60 : 0;

        // Tambahkan ke total jam terpakai jika status finished
        if ($jadwal->status === 'finished') {
            $totalJamTerpakai += $durasiJam;
        }

        // Detail jadwal
        $jadwalDetail[] = [
            'id'            => $jadwal->id,
            'tanggal'       => $jadwal->tanggal,
            'waktu_mulai'   => $jadwal->waktu_mulai,
            'waktu_selesai' => $jadwal->waktu_selesai,
            'status'        => $jadwal->status,
            'durasi_jam'    => $durasiJam
        ];
    }

    // Hitung sisa jam
    $sisaJam = max($totalJamPaket - $totalJamTerpakai, 0);

    return response()->json([
        'success' => true,
        'data' => [
            'id'                 => $pesanan->id,
            'paket'             => $pesanan->paket->nama_paket,
            'jumlah_jam_paket'  => $totalJamPaket,
            'mobil'             => optional($pesanan->mobil)->nama_mobil, // jika relasi mobil ada
            'bukti_pembayaran'  => $pesanan->bukti_pembayaran,
            'status'            => $pesanan->status,
            'total_jam_terpakai' => round($totalJamTerpakai, 2),
            'sisa_jam'          => round($sisaJam, 2),
            'jadwal'            => $jadwalDetail
        ]
    ], 200);
}

}