<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('nama', 'admin')->first();

        // Buat user admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nama' => 'Administrator',
                'password' => Hash::make('password'), 
                'role_id' => $adminRole->id,
                'status_akun' => 'Terverifikasi',
            ]
        );

        Admin::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'nama' => $adminUser->nama,
                'alamat' => 'Jl. Admin Raya No. 1',
            ]
        );
    }
}