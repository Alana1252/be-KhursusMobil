<?php

namespace App\Http\Controllers\Api\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JadwalSiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jadwal = Jadwal::with('pesanan', 'instruktur')->get();
        return response()->json([
            'success' => true,
            'data' => $jadwal
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function tambahJadwal(Pesanan $pesanan)
    {
        $user = Auth::user();
    
        $jadwal = Jadwal::create([
            'pesanan_id' => $pesanan->id,
            'tanggal' => Carbon::today()->toDateString(), // contoh: 2025-05-09
            'waktu_mulai' => Carbon::now()->toTimeString(), // contoh: 14:30:00
            'status' => 'ongoing',
            'instruktur_id' => $user->id
        ]);
    
        return response()->json([
            'success' => true,
            'data' => $jadwal
        ]);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jadwal = Jadwal::with('pesanan', 'instruktur')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $jadwal
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function completedJadwal(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:finished,canceled']
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }
    
        // Ambil jadwal dengan status ongoing dan id sesuai
        $jadwal = Jadwal::where('id', $id)->where('status', 'ongoing')->first();
    
        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal dengan status ongoing tidak ditemukan'
            ], 404);
        }
    
        $jadwal->waktu_selesai = Carbon::now()->toTimeString();
        $jadwal->status = $request->status;
        $jadwal->save();
    
        return response()->json([
            'success' => true,
            'data' => $jadwal
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();
        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }
}
