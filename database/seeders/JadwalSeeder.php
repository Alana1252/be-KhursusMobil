<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jadwal;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Paket;
use Carbon\Carbon;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user instruktur pertama (dengan role instruktur)
        $instruktur = User::role('instruktur')->first();

        // Ambil user siswa pertama (dengan role siswa)
        $siswa = User::role('siswa')->first();

        // Ambil paket pertama
        $paket = Paket::first();

        // Buat pesanan dummy
        $pesanan = Pesanan::create([
            'user_id' => $siswa->id,
            'paket_id' => $paket->id,
            'status' => 'processing'
        ]);

        // Buat jadwal dummy
        Jadwal::create([
            'pesanan_id' => $pesanan->id,
            'instruktur_id' => $instruktur->id,
            'tanggal' => Carbon::today()->toDateString(),
            'waktu_mulai' => Carbon::now()->toTimeString(),
            'waktu_selesai' => Carbon::now()->addHours(2)->toTimeString(),
            'status' => 'ongoing',
        ]);
    }
}
