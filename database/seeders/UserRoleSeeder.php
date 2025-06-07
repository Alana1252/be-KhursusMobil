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
        // ====================
        // 🔐 DAFTAR PERMISSIONS
        // ====================
        $permissions = [
            // Manajemen User
            'lihat-user',
            'tambah-user',
            'edit-user',
            'hapus-user',

            // Manajemen Paket
            'lihat-paket',
            'tambah-paket',
            'edit-paket',
            'hapus-paket',

            // Manajemen Jadwal Siswa
            'lihat-jadwal-siswa',
            'tambah-jadwal-siswa',
            'edit-jadwal-siswa',
            'hapus-jadwal-siswa',
            'lihat-semua-pending-jadwal',

            // Manajemen Pesanan
            'lihat-pesanan',
            'tambah-pesanan',
            'edit-pesanan',
            'hapus-pesanan',
            'lihat-semua-detail'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ===============
        // 🎭 DAFTAR ROLES
        // ===============
        $roles = ['Owner', 'Instruktur', 'Siswa', 'Kasir'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ====================
        // 🔑 SET PERMISSION KE ROLE
        // ====================

        // Role: Siswa
        Role::findByName('Siswa')->syncPermissions([
            'lihat-paket',
            'tambah-pesanan',
            'tambah-jadwal-siswa',
            'lihat-pesanan',
        ]);

        // Role: Instruktur
        Role::findByName('Instruktur')->syncPermissions([
            'lihat-jadwal-siswa',
            'lihat-semua-pending-jadwal',
            'edit-jadwal-siswa',
        ]);

        // Role: Kasir (akses penuh terhadap paket, jadwal, dan pesanan)
        Role::findByName('Kasir')->syncPermissions($permissions);

        // Role: Owner (akses penuh semua permission)
        Role::findByName('Owner')->syncPermissions(Permission::all());

        // ====================
        // 👤 BUAT USER DEFAULT
        // ====================
        $users = [
            ['name' => 'Owner', 'username' => 'owner', 'no_hp' => '081234567890', 'email' => 'owner@example.com', 'role' => 'Owner', 'password' => 'owner'],
            ['name' => 'Instruktur', 'username' => 'instruktur', 'no_hp' => '081234567891', 'email' => 'instruktur@example.com', 'role' => 'Instruktur', 'password' => 'instruktur'],
            ['name' => 'Siswa', 'username' => 'siswa', 'no_hp' => '081234567892', 'email' => 'siswa@example.com', 'role' => 'Siswa', 'password' => 'siswa'],
            ['name' => 'Kasir', 'username' => 'kasir', 'no_hp' => '081234567893', 'email' => 'kasir@example.com', 'role' => 'Kasir', 'password' => 'kasir'],
        ];

        foreach ($users as $data) {
            $user = User::factory()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'no_hp' => $data['no_hp'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole($data['role']);
        }

        // =====================
        // 📦 CONTOH DATA PAKET
        // =====================
        DB::table('paket')->insert([
            [
                'nama_paket' => 'Paket Reguler',
                'jumlah_jam' => 15,
                'deskripsi' => 'Paket belajar reguler selama 15 jam. Senin - Jumat',
                'no_rekening' => '123456789',
                'harga' => 2300000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Private',
                'jumlah_jam' => 15,
                'deskripsi' => 'Paket belajar private selama 15 jam. Setiap hari',
                'no_rekening' => '123456789',
                'harga' => 2350000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}