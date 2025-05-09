<?php

namespace App\Http\Controllers\Api\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paket;
use Illuminate\Support\Facades\Validator;

class PaketController extends Controller
{
    public function index()
    {
        return response()->json([   
            'success' => true,
            'data' => Paket::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_paket' => ['required', 'string'],
            'jumlah_jam' => ['required', 'string'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $paket = Paket::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil dibuat',
            'paket' => $paket
        ], 201);
    }

    public function show(Paket $paket)
    {
        return response()->json([
            'success' => true,
            'data' => $paket
        ]);
    }

    public function update(Request $request, Paket $paket)
    {
        $validator = Validator::make($request->all(), [
            'nama_paket' => ['required', 'string'],
            'jumlah_jam' => ['required', 'string'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $paket->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil diperbarui',
            'paket' => $paket
        ]);
    }

    public function destroy(Paket $paket)
    {
        $paket->delete();
        return response()->json([
            'success' => true,
            'message' => 'Paket deleted'
        ]);
    }
}
