<?php

namespace Database\Seeders;

use App\Models\KategoriAlat;
use Illuminate\Database\Seeder;

class KategoriAlatSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            [
                'nama_kategori' => 'Kamera DSLR',
                'deskripsi'     => 'Kamera DSLR professional dan semi-professional',
                'icon'          => 'camera',
            ],
            [
                'nama_kategori' => 'Kamera Mirrorless',
                'deskripsi'     => 'Kamera mirrorless modern dengan teknologi terkini',
                'icon'          => 'camera-alt',
            ],
            [
                'nama_kategori' => 'Lensa',
                'deskripsi'     => 'Lensa untuk berbagai kebutuhan fotografi',
                'icon'          => 'lens',
            ],
            [
                'nama_kategori' => 'Lighting',
                'deskripsi'     => 'Peralatan pencahayaan untuk studio dan outdoor',
                'icon'          => 'lightbulb',
            ],
            [
                'nama_kategori' => 'Tripod & Support',
                'deskripsi'     => 'Tripod, monopod, dan aksesoris penyangga kamera',
                'icon'          => 'support',
            ],
            [
                'nama_kategori' => 'Audio',
                'deskripsi'     => 'Microphone dan peralatan audio recording',
                'icon'          => 'mic',
            ],
            [
                'nama_kategori' => 'Aksesoris',
                'deskripsi'     => 'Aksesoris pendukung fotografi dan videografi',
                'icon'          => 'extension',
            ],
        ];

        foreach ($kategoris as $kategori) {
            KategoriAlat::create($kategori);
        }

        $this->command->info('✓ Kategori alat seeded successfully');
    }
}