<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function lihatSemuaDetail()
    {
        $owner = Auth::user();
        $pesanan = Pesanan::with(['paket', 'user', 'jadwal'])->get();
        return response()->json([
            'success' => true,
            'message' => 'Owner data retrieved successfully',
            'data' => [
                'nama_pemesan' => $pesanan->user->name,
                'nama_paket' => $pesanan->paket->nama_paket,
                'tanggal_pemesanan' => $pesanan->tanggal_pemesanan,
                'status_pemesanan' => $pesanan->status_pemesanan,
            ]
        ]);
    }
}
