<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; 
use Database\Seeders\RoleSeeder;
use Database\Seeders\AdminUserSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        
            AdminUserSeeder::class,
        ]);

        User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}