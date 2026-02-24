<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\KategoriAlat;
use Illuminate\Database\Seeder;

class AlatSeeder extends Seeder
{
    public function run(): void
    {
        $kamerasDslr = KategoriAlat::where('nama_kategori', 'Kamera DSLR')->first();
        $kamerasMirrorless = KategoriAlat::where('nama_kategori', 'Kamera Mirrorless')->first();
        $lensa = KategoriAlat::where('nama_kategori', 'Lensa')->first();
        $lighting = KategoriAlat::where('nama_kategori', 'Lighting')->first();
        $tripod = KategoriAlat::where('nama_kategori', 'Tripod & Support')->first();

        $alats = [
            // Kamera DSLR
            [
                'kode_alat'          => 'CAM-DSLR-001',
                'nama_alat'          => 'Canon EOS 80D',
                'id_kategori'        => $kamerasDslr->id,
                'merk'               => 'Canon',
                'tipe'               => 'EOS 80D',
                'serial_number'      => 'CN80D2021001',
                'tahun_pembelian'    => 2021,
                'harga_beli'         => 15000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => '24.2 MP, CMOS Sensor, Full HD Video',
                'lokasi_penyimpanan' => 'Rak A1',
            ],
            [
                'kode_alat'          => 'CAM-DSLR-002',
                'nama_alat'          => 'Nikon D750',
                'id_kategori'        => $kamerasDslr->id,
                'merk'               => 'Nikon',
                'tipe'               => 'D750',
                'serial_number'      => 'NK750202202',
                'tahun_pembelian'    => 2022,
                'harga_beli'         => 18000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => '24.3 MP, Full Frame, Dual SD Card',
                'lokasi_penyimpanan' => 'Rak A1',
            ],
            
            // Kamera Mirrorless
            [
                'kode_alat'          => 'CAM-MIR-001',
                'nama_alat'          => 'Sony A7 III',
                'id_kategori'        => $kamerasMirrorless->id,
                'merk'               => 'Sony',
                'tipe'               => 'A7 III',
                'serial_number'      => 'SNA7III2023',
                'tahun_pembelian'    => 2023,
                'harga_beli'         => 25000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => '24.2 MP, Full Frame, 4K Video, Eye AF',
                'lokasi_penyimpanan' => 'Rak A2',
            ],
            [
                'kode_alat'          => 'CAM-MIR-002',
                'nama_alat'          => 'Fujifilm X-T4',
                'id_kategori'        => $kamerasMirrorless->id,
                'merk'               => 'Fujifilm',
                'tipe'               => 'X-T4',
                'serial_number'      => 'FJXT42023001',
                'tahun_pembelian'    => 2023,
                'harga_beli'         => 22000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => '26.1 MP, APS-C, 4K 60fps, IBIS',
                'lokasi_penyimpanan' => 'Rak A2',
            ],

            // Lensa
            [
                'kode_alat'          => 'LENS-001',
                'nama_alat'          => 'Canon EF 50mm f/1.8 STM',
                'id_kategori'        => $lensa->id,
                'merk'               => 'Canon',
                'tipe'               => 'EF 50mm f/1.8 STM',
                'serial_number'      => 'CN50MM2021',
                'tahun_pembelian'    => 2021,
                'harga_beli'         => 1500000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => 'Prime lens, f/1.8, Bokeh effect',
                'lokasi_penyimpanan' => 'Rak B1',
            ],
            [
                'kode_alat'          => 'LENS-002',
                'nama_alat'          => 'Canon EF 24-70mm f/2.8L II USM',
                'id_kategori'        => $lensa->id,
                'merk'               => 'Canon',
                'tipe'               => 'EF 24-70mm f/2.8L II',
                'serial_number'      => 'CN2470MM22',
                'tahun_pembelian'    => 2022,
                'harga_beli'         => 20000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => 'Zoom lens, f/2.8, Weather sealed',
                'lokasi_penyimpanan' => 'Rak B1',
            ],
            [
                'kode_alat'          => 'LENS-003',
                'nama_alat'          => 'Sony FE 85mm f/1.8',
                'id_kategori'        => $lensa->id,
                'merk'               => 'Sony',
                'tipe'               => 'FE 85mm f/1.8',
                'serial_number'      => 'SN85MM2023',
                'tahun_pembelian'    => 2023,
                'harga_beli'         => 7000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => 'Portrait lens, Fast AF, Compact',
                'lokasi_penyimpanan' => 'Rak B2',
            ],

            // Lighting
            [
                'kode_alat'          => 'LIGHT-001',
                'nama_alat'          => 'Godox SL-60W LED Video Light',
                'id_kategori'        => $lighting->id,
                'merk'               => 'Godox',
                'tipe'               => 'SL-60W',
                'serial_number'      => 'GDX60W2022',
                'tahun_pembelian'    => 2022,
                'harga_beli'         => 2500000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => '60W LED, Bowens Mount, 5600K',
                'lokasi_penyimpanan' => 'Rak C1',
            ],
            [
                'kode_alat'          => 'LIGHT-002',
                'nama_alat'          => 'Aputure 120D Mark II',
                'id_kategori'        => $lighting->id,
                'merk'               => 'Aputure',
                'tipe'               => '120D Mark II',
                'serial_number'      => 'APT120D23',
                'tahun_pembelian'    => 2023,
                'harga_beli'         => 8000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => '120W COB LED, Wireless control, CRI 96+',
                'lokasi_penyimpanan' => 'Rak C1',
            ],

            // Tripod
            [
                'kode_alat'          => 'TRIP-001',
                'nama_alat'          => 'Manfrotto MT055XPRO3',
                'id_kategori'        => $tripod->id,
                'merk'               => 'Manfrotto',
                'tipe'               => 'MT055XPRO3',
                'serial_number'      => 'MF055X2021',
                'tahun_pembelian'    => 2021,
                'harga_beli'         => 4000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => 'Aluminium, Max height 170cm, Load 9kg',
                'lokasi_penyimpanan' => 'Rak D1',
            ],
            [
                'kode_alat'          => 'TRIP-002',
                'nama_alat'          => 'Benro Mach3 Carbon Fiber',
                'id_kategori'        => $tripod->id,
                'merk'               => 'Benro',
                'tipe'               => 'Mach3 CF',
                'serial_number'      => 'BNR3CF2022',
                'tahun_pembelian'    => 2022,
                'harga_beli'         => 6000000,
                'kondisi'            => 'baik',
                'status'             => 'tersedia',
                'spesifikasi'        => 'Carbon fiber, Lightweight, Load 18kg',
                'lokasi_penyimpanan' => 'Rak D1',
            ],
        ];

        foreach ($alats as $alat) {
            Alat::create($alat);
        }

        $this->command->info('✓ Alat seeded successfully');
    }
}