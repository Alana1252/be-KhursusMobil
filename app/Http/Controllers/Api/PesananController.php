<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pesanan ;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
                'jumlah_jam_paket' => $totalJamPaket,
                'jam_terpakai' => round($totalJamTerpakai, 2),
                'jam_sisa' => round(max($sisaJam, 0), 2),
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

        $validator = Validator::make($request->all(), [
            'id_paket' => ['required', 'exists:paket,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $pesanan = Pesanan::create([
            'paket_id' => $request->id_paket,
            'user_id' => $user->id,
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
        $pesanan = Pesanan::with('paket', 'user', 'jadwal')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $pesanan
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
