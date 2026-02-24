<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Admin
            [
                'username'          => 'admin',
                'password'          => Hash::make('admin123'),
                'email'             => 'admin@photoin.com',
                'nama_lengkap'      => 'Administrator PhotoIn',
                'role'              => 'admin',
                'no_hp'             => '081234567890',
                'alamat'            => 'Jl. Brawijaya No. 123, Probolinggo',
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ],
            
            // Petugas 1
            [
                'username'          => 'petugas1',
                'password'          => Hash::make('petugas123'),
                'email'             => 'petugas1@photoin.com',
                'nama_lengkap'      => 'Budi Santoso',
                'role'              => 'petugas',
                'no_hp'             => '082345678901',
                'alamat'            => 'Jl. Suroyo No. 45, Probolinggo',
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ],
            
            // Petugas 2
            [
                'username'          => 'petugas2',
                'password'          => Hash::make('petugas123'),
                'email'             => 'petugas2@photoin.com',
                'nama_lengkap'      => 'Siti Nurhaliza',
                'role'              => 'petugas',
                'no_hp'             => '083456789012',
                'alamat'            => 'Jl. Pahlawan No. 67, Probolinggo',
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ],
            
            // Penyewa 1
            [
                'username'          => 'penyewa1',
                'password'          => Hash::make('penyewa123'),
                'email'             => 'john.doe@gmail.com',
                'nama_lengkap'      => 'John Doe',
                'role'              => 'penyewa',
                'no_hp'             => '085678901234',
                'alamat'            => 'Jl. Merdeka No. 10, Probolinggo',
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ],
            
            // Penyewa 2
            [
                'username'          => 'penyewa2',
                'password'          => Hash::make('penyewa123'),
                'email'             => 'jane.smith@gmail.com',
                'nama_lengkap'      => 'Jane Smith',
                'role'              => 'penyewa',
                'no_hp'             => '086789012345',
                'alamat'            => 'Jl. Sudirman No. 25, Probolinggo',
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ],
            
            // Penyewa 3
            [
                'username'          => 'penyewa3',
                'password'          => Hash::make('penyewa123'),
                'email'             => 'ahmad.rizki@yahoo.com',
                'nama_lengkap'      => 'Ahmad Rizki',
                'role'              => 'penyewa',
                'no_hp'             => '087890123456',
                'alamat'            => 'Jl. Diponegoro No. 88, Probolinggo',
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('✓ Users seeded successfully');
        $this->command->line('');
        $this->command->info('=== Login Credentials ===');
        $this->command->line('Admin     : admin / admin123');
        $this->command->line('Petugas 1 : petugas1 / petugas123');
        $this->command->line('Petugas 2 : petugas2 / petugas123');
        $this->command->line('Penyewa 1 : penyewa1 / penyewa123');
        $this->command->line('Penyewa 2 : penyewa2 / penyewa123');
        $this->command->line('Penyewa 3 : penyewa3 / penyewa123');
    }
}