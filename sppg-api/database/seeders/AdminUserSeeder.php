<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sppg.local'],
            [
                'name' => 'Administrator SPPG',
                'password' => Hash::make('AdminSPPG123!'),
                'role' => 'admin',
                'account_status' => 'active',
                'approved_by' => null,
                'approved_at' => now(),
            ]
        );
    }
}
