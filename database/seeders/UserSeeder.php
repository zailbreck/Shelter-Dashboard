<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@shelter.local'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('Admin@123!'),
                'google2fa_enabled' => false,
            ]
        );
    }
}
