<?php

namespace Database\Seeders;

use App\Models\AturanDenda;
use Illuminate\Database\Seeder;

class AturanDendaSeeder extends Seeder
{
    public function run(): void
    {
        $aturanDendas = [
            [
                'jenis_denda' => 'keterlambatan',
                'nominal'     => 50000,
                'satuan'      => 'per hari',
                'deskripsi'   => 'Denda keterlambatan pengembalian alat per hari',
                'is_active'   => true,
            ],
            [
                'jenis_denda' => 'kerusakan_ringan',
                'nominal'     => 200000,
                'satuan'      => 'per alat',
                'deskripsi'   => 'Denda untuk kerusakan ringan pada alat (goresan, lecet minor)',
                'is_active'   => true,
            ],
            [
                'jenis_denda' => 'kerusakan_berat',
                'nominal'     => 1000000,
                'satuan'      => 'per alat',
                'deskripsi'   => 'Denda untuk kerusakan berat pada alat (tidak berfungsi, rusak parah)',
                'is_active'   => true,
            ],
            [
                'jenis_denda' => 'kehilangan_alat',
                'nominal'     => 0, // Akan dihitung berdasarkan harga beli alat
                'satuan'      => 'harga beli',
                'deskripsi'   => 'Denda kehilangan alat sebesar harga beli alat',
                'is_active'   => true,
            ],
            [
                'jenis_denda' => 'kehilangan_kelengkapan',
                'nominal'     => 100000,
                'satuan'      => 'per item',
                'deskripsi'   => 'Denda kehilangan kelengkapan alat (charger, kabel, tutup lensa, dll)',
                'is_active'   => true,
            ],
        ];

        foreach ($aturanDendas as $aturan) {
            AturanDenda::create($aturan);
        }

        $this->command->info('✓ Aturan denda seeded successfully');
    }
}