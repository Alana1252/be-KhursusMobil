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
        $instruktur = User::role('instruktur')->first();
        $siswa = User::role('siswa')->get();
        $paket = Paket::all();
        $mobil = ['Toyota Avanza', 'Honda Brio', 'Suzuki Ertiga', 'Daihatsu Xenia', 'Nissan Grand Livina'];

        for ($i = 0; $i < 6; $i++) {
            $pesanan = Pesanan::create([
                'user_id' => $siswa->random()->id,
                'paket_id' => $paket->random()->id,
                'mobil' => $mobil[array_rand($mobil)],
                'status' => ['pending', 'success', 'processing'][array_rand(['pending', 'success', 'processing'])]
            ]);

            for ($j = 0; $j < 7; $j++) {
                $startDate = Carbon::now()->addDays(rand(1, 30));
                Jadwal::create([
                    'pesanan_id' => $pesanan->id,
                    'instruktur_id' => $instruktur->id,
                    'tanggal' => $startDate->toDateString(),
                    'waktu_mulai' => $startDate->format('H:i:s'),
                    'waktu_selesai' => $startDate->addHours(2)->format('H:i:s'),
                    'status' => ['pending', 'ongoing', 'finished'][array_rand(['pending', 'ongoing', 'finished'])],
                ]);
            }
        }
    }
}
