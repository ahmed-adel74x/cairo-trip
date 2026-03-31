<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cairotrip.com'],
            [
                'name'               => 'مدير النظام',
                'email'              => 'admin@cairotrip.com',
                'password'           => Hash::make('admin123'),
                'role'               => 'admin',
                'preferred_language' => 'ar',
                'phone'              => '01000000000',
            ]
        );

        $this->command->info('✅ Admin user created: admin@cairotrip.com / admin123');
    }
}