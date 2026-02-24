<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\HargaSewa;
use Illuminate\Database\Seeder;

class HargaSewaSeeder extends Seeder
{
    public function run(): void
    {
        $alats = Alat::all();

        foreach ($alats as $alat) {
            // Harga dasar berdasarkan harga beli
            $hargaBeli = $alat->harga_beli;
            
            // Hitung harga sewa (2-3% dari harga beli per hari)
            $harga1Hari = ceil(($hargaBeli * 0.025) / 10000) * 10000; // Dibulatkan ke 10rb
            $harga3Hari = $harga1Hari * 3 * 0.9; // Diskon 10%
            $harga7Hari = $harga1Hari * 7 * 0.85; // Diskon 15%

            // Paket 1 hari
            HargaSewa::create([
                'id_alat'         => $alat->id,
                'durasi_hari'     => 1,
                'harga'           => $harga1Hari,
                'is_paket'        => false,
                'nama_paket'      => null,
                'deskripsi_paket' => null,
                'is_active'       => true,
            ]);

            // Paket 3 hari
            HargaSewa::create([
                'id_alat'         => $alat->id,
                'durasi_hari'     => 3,
                'harga'           => $harga3Hari,
                'is_paket'        => true,
                'nama_paket'      => 'Paket 3 Hari',
                'deskripsi_paket' => 'Hemat 10% untuk sewa 3 hari',
                'is_active'       => true,
            ]);

            // Paket 7 hari (mingguan)
            HargaSewa::create([
                'id_alat'         => $alat->id,
                'durasi_hari'     => 7,
                'harga'           => $harga7Hari,
                'is_paket'        => true,
                'nama_paket'      => 'Paket Mingguan',
                'deskripsi_paket' => 'Hemat 15% untuk sewa seminggu',
                'is_active'       => true,
            ]);
        }

        $this->command->info('✓ Harga sewa seeded successfully');
    }
}