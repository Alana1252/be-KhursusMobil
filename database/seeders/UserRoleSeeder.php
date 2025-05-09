<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Semua permission berdasarkan route
        $permissions = [
            // User
            'lihat-user',
            'tambah-user',
            'edit-user',
            'hapus-user',

            // Paket
            'lihat-paket',
            'tambah-paket',
            'edit-paket',
            'hapus-paket',

            // Jadwal Siswa
            'lihat-jadwal-siswa',
            'tambah-jadwal-siswa',
            'edit-jadwal-siswa',
            'hapus-jadwal-siswa',

            // Pesanan
            'lihat-pesanan',
            'tambah-pesanan',
            'edit-pesanan',
            'hapus-pesanan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat roles
        $roles = ['Owner', 'Instruktur', 'Siswa', 'Kasir'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Role: Siswa
        Role::findByName('Siswa')->givePermissionTo([
            'lihat-paket',              // Lihat paket
            'tambah-pesanan',           // Pesan paket
            'tambah-jadwal-siswa',      // Pilih jadwal
        ]);

        // Role: Instruktur
        Role::findByName('Instruktur')->givePermissionTo([
            'lihat-jadwal-siswa',       // Lihat semua jadwal
            'edit-jadwal-siswa',        // Ambil jadwal dan ubah statusnya
        ]);

        // Role: Kasir
        Role::findByName('Kasir')->givePermissionTo([
            $permissions
        ]);

        // Role: Owner
        Role::findByName('Owner')->givePermissionTo([
            'lihat-jadwal-siswa',
        ]);


        // Create Users
        $users = [
            ['name' => 'Owner', 'username' => 'owner', 'no_hp' => '081234567890', 'email' => 'owner@example.com', 'role' => 'Owner', 'password' => 'owner'],
            ['name' => 'Instruktur', 'username' => 'instruktur', 'no_hp' => '081234567891', 'email' => 'instruktur@example.com', 'role' => 'Instruktur', 'password' => 'instruktur'],
            ['name' => 'Siswa', 'username' => 'siswa', 'no_hp' => '081234567892', 'email' => 'siswa@example.com', 'role' => 'Siswa', 'password' => 'siswa'],
            ['name' => 'Kasir', 'username' => 'kasir', 'no_hp' => '081234567893', 'email' => 'kasir@example.com', 'role' => 'Kasir', 'password' => 'kasir'],
        ];

        foreach ($users as $userData) {
            $user = User::factory()->create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'no_hp' => $userData['no_hp'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            $user->assignRole($userData['role']);
        }

        // Insert Paket
        DB::table('paket')->insert([
            [
                'nama_paket' => 'Paket Reguler',
                'jumlah_jam' => '15',
                'deskripsi' => 'Paket belajar reguler selama 15 jam. Senin - Jumat',
                'harga' => 2300000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Private',
                'jumlah_jam' => '15',
                'deskripsi' => 'Paket belajar private selama 15 jam. Setiap hari',
                'harga' => 2350000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
