<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@reliancegranito.com'],
            [
                'name' => 'Rajesh B. Patel',
                'password' => Hash::make('Reliance@2026'),
                'role_title' => 'Export Documentation Manager',
                'avatar' => 'RP',
            ]
        );
    }
}
