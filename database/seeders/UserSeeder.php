<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nama_lengkap' => 'Admin Utama',
                'email' => 'admin@mail.com',
                'password' => 'admin123',
                'role' => 'admin',
            ],

            ['nama_lengkap' => 'Budi Santoso',   'email' => 'budi@mail.com'],
            ['nama_lengkap' => 'Sari Wijaya',    'email' => 'sari@mail.com'],
            ['nama_lengkap' => 'Doni Saputra',   'email' => 'doni@mail.com'],
            ['nama_lengkap' => 'Lina Maulida',   'email' => 'lina@mail.com'],
            ['nama_lengkap' => 'Ahmad Rizal',    'email' => 'rizal@mail.com'],
            ['nama_lengkap' => 'Wulan Setiawan', 'email' => 'wulan@mail.com'],
            ['nama_lengkap' => 'Ayu Lestari',    'email' => 'ayu@mail.com'],
            ['nama_lengkap' => 'Raka Pratama',   'email' => 'raka@mail.com'],
            ['nama_lengkap' => 'Karin Putri',    'email' => 'karin@mail.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'nama_lengkap' => $user['nama_lengkap'],
                    'password' => Hash::make($user['password'] ?? 'user123'),
                    'role' => $user['role'] ?? 'karyawan',
                ]
            );
        }
    }
}