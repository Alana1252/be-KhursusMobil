<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function lihatSemuaDetail()
    {
        $groupedPesanan = Pesanan::with(['user', 'paket', 'jadwal'])
            ->get()
            ->groupBy('status'); // Mengelompokkan berdasarkan status pesanan
    
        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil diambil dan dikelompokkan berdasarkan status',
            'data' => $groupedPesanan,
        ]);
    }
    

}
