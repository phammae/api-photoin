<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->line('');

        // Urutan penting: user & kategori dulu, baru alat, baru harga & aturan
        $this->call([
            UserSeeder::class,
            KategoriAlatSeeder::class,
            AlatSeeder::class,
            HargaSewaSeeder::class,
            AturanDendaSeeder::class,
        ]);

        $this->command->line('');
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->line('');
        $this->command->info('Quick Start:');
        $this->command->line('1. Login as Admin   : admin / admin123');
        $this->command->line('2. Login as Petugas : petugas1 / petugas123');
        $this->command->line('3. Login as Penyewa : penyewa1 / penyewa123');
        $this->command->line('');
        $this->command->info('API Endpoint: http://localhost:8000/api');
    }
}