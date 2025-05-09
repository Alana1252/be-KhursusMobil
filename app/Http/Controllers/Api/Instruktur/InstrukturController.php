<?php

namespace App\Http\Controllers\Api\Instruktur;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InstrukturController extends Controller
{
    
    public function semuaMuridWithPesananAndPendingJadwal()
    {
     
    $muridDenganJadwalPending = User::whereHas('pesanan', function ($query) {
        $query->where('status', 'success') // hanya pesanan sukses
              ->whereHas('jadwal', function ($q) {
                  $q->where('status', 'pending'); // dan jadwal pending
              });
    })
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Siswa'); // pakai Spatie role
        })
        ->with(['pesanan.jadwal' => function ($query) {
            $query->where('status', 'pending');
        }])
        ->get();
    
        return response()->json([
            'success' => true,
            'data' => $muridDenganJadwalPending
        ]);
    }
    

    public function updateJadwalStatus(Request $request, Jadwal $jadwal)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:ongoing,finished,canceled']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $jadwal = Jadwal::findOrFail($jadwal->id);

        if ($request->status === 'ongoing' && $jadwal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal harus berstatus pending untuk diubah menjadi ongoing.',
                'status' => $jadwal->status
            ], 422);
        }

        if ($request->status === 'canceled' && $jadwal->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal yang sudah finished tidak dapat dibatalkan.',
                'status' => $jadwal->status
            ], 422);
        }

        if ($request->status === 'finished' && $jadwal->status !== 'ongoing') {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal harus berstatus ongoing untuk diubah menjadi finished.',
                'status' => $jadwal->status
            ], 422);
        }

        $jadwal->instruktur_id = Auth::user()->id;
        $jadwal->status = $request->status;
        $jadwal->save();

        return response()->json([
            'success' => true,
            'message' => 'Status jadwal berhasil diperbarui'
        ]);
    }

}
