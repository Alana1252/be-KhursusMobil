<?php

namespace App\Http\Controllers\Api\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PesananController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $pesananList = Pesanan::with(['paket', 'user', 'jadwal'])->get();

        $result = $pesananList->map(function ($pesanan) {
            $jadwalList = $pesanan->jadwal ?? collect();

            $totalJamTerpakai = $jadwalList->where('status', 'finished')->reduce(function ($carry, $jadwal) {
                if ($jadwal->waktu_mulai && $jadwal->waktu_selesai) {
                    try {
                        $mulai = Carbon::createFromFormat('H:i:s', $jadwal->waktu_mulai);
                        $selesai = Carbon::createFromFormat('H:i:s', $jadwal->waktu_selesai);
                        $jam = $selesai->diffInMinutes($mulai) / 60;
                        return $carry + $jam;
                    } catch (\Exception $e) {
                        // Log jika error parsing
                        logger('Error parsing waktu: ' . $e->getMessage());
                    }
                }
                return $carry;
            }, 0);

            $totalJamPaket = (float) $pesanan->paket->jumlah_jam;
            $sisaJam = $totalJamPaket - $totalJamTerpakai;

            return [
                'pesanan_id' => $pesanan->id,
                'nama_user' => $pesanan->user->name,
                'nama_paket' => $pesanan->paket->nama_paket,
                'status' => $pesanan->status,
                'jumlah_jam_paket' => $totalJamPaket,
                'mobil' => $pesanan->mobil,
                'jam_terpakai' => round($totalJamTerpakai, 2),
                'jam_sisa' => round(max($sisaJam, 0), 2),
                'bukti_pembayaran' => env('APP_STORAGE') . $pesanan->bukti_pembayaran,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // ✅ CEK apakah user masih memiliki pesanan aktif (pending atau processing)
        $existing = Pesanan::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu masih memiliki pesanan yang belum selesai.',
                'pesanan' => $existing,
            ], 409); // HTTP 409 Conflict
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_paket' => ['required', 'exists:paket,id'],
            'mobil' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // Simpan pesanan baru
        $pesanan = Pesanan::create([
            'paket_id' => $request->id_paket,
            'user_id' => $user->id,
            'mobil' => $request->mobil,
            'status' => 'pending'
        ]);

        if ($pesanan) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'pesanan' => $pesanan
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesanan gagal dibuat'
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pesanan = Pesanan::with('paket', 'user', 'jadwal.instruktur')->findOrFail($id);
        $jadwalList = $pesanan->jadwal ?? collect();
        $totalJamTerpakai = 0;
        $jadwalDetails = [];

        foreach ($jadwalList as $jadwal) {
            if ($jadwal->waktu_mulai && $jadwal->waktu_selesai) {
                try {
                    $mulai = Carbon::createFromFormat('H:i:s', $jadwal->waktu_mulai);
                    $selesai = Carbon::createFromFormat('H:i:s', $jadwal->waktu_selesai);
                    $jamPerjadwal = $selesai->diffInMinutes($mulai) / 60;
                    $totalJamTerpakai += $jadwal->status === 'finished' ? $jamPerjadwal : 0;

                    $jadwalDetails[] = [
                        'instruktur' => $jadwal->instruktur->name,
                        'waktu_mulai' => $jadwal->waktu_mulai,
                        'waktu_selesai' => $jadwal->waktu_selesai,
                        'jumlah_jam' => round($jamPerjadwal, 2),
                        'status' => $jadwal->status
                    ];
                } catch (\Exception $e) {
                    Log::error('Error parsing waktu: ' . $e->getMessage());
                }
            }
        }

        $totalJamPaket = (float) $pesanan->paket->jumlah_jam;
        $sisaJam = $totalJamPaket - $totalJamTerpakai;

        return response()->json([
            'success' => true,
            'data' => [
                'pesanan_id' => $pesanan->id,
                'nama_user' => $pesanan->user->name,
                'nama_paket' => $pesanan->paket->nama_paket,
                'status' => $pesanan->status,
                'jumlah_jam_paket' => $totalJamPaket,
                'jam_terpakai' => round($totalJamTerpakai, 2),
                'jam_sisa' => round(max($sisaJam, 0), 2),
                'bukti_pembayaran' => env('APP_STORAGE') . $pesanan->bukti_pembayaran,
                'jadwal' => $jadwalDetails
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:pending,processing,success,failed']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diperbarui',
            'pesanan' => $pesanan
        ]);
    }

    public function changeStatus(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:pending,processing,success,failed']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diperbarui',
            'pesanan' => $pesanan
        ]);
    }

    public function uploadBukti(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'bukti' => ['required', 'image', 'max:2048'] // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $pesanan = Pesanan::findOrFail($id);

        // Simpan file ke storage/app/public/buktipembayaran dengan nama acak
        $file = $request->file('bukti');
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('buktipembayaran', $filename, 'public');

        // Simpan path ke database (jika ingin hanya nama file, cukup $filename)
        $pesanan->bukti_pembayaran = $path;
        $pesanan->save();

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload',
            'pesanan' => $pesanan
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dihapus',
            'pesanan' => $pesanan
        ]);
    }
}
