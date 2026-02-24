<?php

namespace App\Services;

use App\Models\AturanDenda;
use App\Models\DetailDenda;
use App\Models\LogAktivitas;
use App\Repositories\Contracts\AlatRepositoryInterface;
use App\Repositories\Contracts\PeminjamanRepositoryInterface;
use App\Repositories\Contracts\PengembalianRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PengembalianService
{
    public function __construct(
        private PengembalianRepositoryInterface $pengembalianRepository,
        private PeminjamanRepositoryInterface $peminjamanRepository,
        private AlatRepositoryInterface $alatRepository
    ) {}

    // Get daftar pengembalian

    public function getAll(array $filters, int $perPage): array
    {
        $pengembalian = $this->pengembalianRepository->getAll($filters, $perPage);

        return [
            'success' => true,
            'data'    => $pengembalian,
        ];
    }

    // Get detail pengembalian
    public function getById(int $id): array
    {
        $pengembalian = $this->pengembalianRepository->findById($id);

        if (!$pengembalian) {
            throw new \Exception('Pengembalian tidak ditemukan', 404);
        }

        return [
            'success' => true,
            'data'    => $pengembalian,
        ];
    }

    // Proses pengembalian & hitung denda otomatis
    public function process(int $idPeminjaman, array $data, int $petugasId): array
    {
        DB::beginTransaction();

        try {
            // Validasi peminjaman
            $peminjaman = $this->peminjamanRepository->findById($idPeminjaman);

            if (!$peminjaman) {
                throw new \Exception('Peminjaman tidak ditemukan', 404);
            }

            if (!$peminjaman->isDipinjam()) {
                throw new \Exception('Peminjaman tidak dalam status dipinjam', 400);
            }

            // Cek apakah sudah ada pengembalian
            if ($this->pengembalianRepository->findByPeminjamanId($idPeminjaman)) {
                throw new \Exception('Peminjaman ini sudah dikembalikan', 400);
            }

            // Hitung hari terlambat
            $tanggalKembali = $data['tanggal_kembali'] ?? now()->toDateString();
            $hariTerlambat = $peminjaman->tanggal_kembali_rencana->diffInDays($tanggalKembali, false);
            $hariTerlambat = max(0, (int) $hariTerlambat);

            // Pengembalian
            $pengembalian = $this->pengembalianRepository->create([
                'id_peminjaman'  => $idPeminjaman,
                'id_petugas'     => $petugasId,
                'tanggal_kembali' => $tanggalKembali,
                'hari_terlambat' => $hariTerlambat,
                'total_denda'    => 0, // akan dihitung nanti
                'catatan'        => $data['catatan'] ?? null,
            ]);

            // Hitung denda per alat
            $totalDenda = 0;
            $detailDendaArray = [];

            foreach ($peminjaman->details as $detail) {
                $alatId = $detail->id_alat;
                $kondisiKembali = $data['alat'][$alatId]['kondisi'] ?? 'baik';
                $kelengkapanHilang = $data['alat'][$alatId]['kelengkapan_hilang'] ?? [];

                // Denda keterlambatan
                if ($hariTerlambat > 0) {
                    $dendaKeterlambatan = $this->hitungDendaKeterlambatan(
                        $pengembalian->id,
                        $alatId,
                        $hariTerlambat
                    );
                    $totalDenda = $dendaKeterlambatan['total'];
                    $detailDendaArray[] = $dendaKeterlambatan['detail'];
                }

                // Denda kerusakan
                if ($kondisiKembali !== 'baik') {
                    $dendaKerusakan = $this->hitungDendaKerusakan(
                        $pengembalian->id,
                        $alatId,
                        $kondisiKembali,
                        $data['alat'][$alatId]['keterangan'] ?? null
                    );
                    $totalDenda += $dendaKerusakan['total'];
                    $detailDendaArray[] = $dendaKerusakan['detail'];
                }

                // Denda kelengkapan hilang
                if (!empty($kelengkapanHilang)) {
                    foreach ($kelengkapanHilang as $kelengkapan) {
                        $dendaKelengkapan = $this->hitungDendaKehilanganKelengkapan(
                            $pengembalian->id,
                            $alatId,
                            $kelengkapan
                        );
                        $totalDenda += $dendaKelengkapan['total'];
                        $detailDendaArray[] = $dendaKelengkapan['detail'];
                    }
                }

                // update status alat menjadi tersedia
                $this->alatRepository->updateStatus($detail->alat, 'tersedia');
            }
            
            // Update total denda di pengembalian
            $this->pengembalianRepository->update($pengembalian, [
                'total_denda' => $totalDenda,
            ]);

            // Update peminjaman
            $this->peminjamanRepository->updateStatus($peminjaman, 'dikembalikan', [
                'tanggal_kembali_aktual' => $tanggalKembali,
                'total_denda'            => $totalDenda,
            ]);

            LogAktivitas::log(
                $petugasId,
                'PROCESS_PENGEMBALIAN',
                "Memproses pengembalian: {$peminjaman->kode_peminjaman}",
                'pengembalian',
                $pengembalian->id
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pengembalian berhasil diproses',
                'data'    => [
                    'pengembalian'  => $pengembalian->fresh([
                        'peminjaman.details.alat',
                        'detailDenda.alat',
                        'detailDenda.aturanDenda'
                    ]),
                    'total_denda'   => $totalDenda,
                    'hari_terlambat' => $hariTerlambat,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Hitung denda keterlambatan
    private function hitungDendaKeterlambatan(int $idPengembalian, int $idAlat, int $hariTerlambat): array
    {
        $aturan = AturanDenda::getAktif('keterlambatan');

        if (!$aturan) {
            throw new \Exception('Aturan denda keterlambatan tidak ditemukan', 500);
        }

        $nominalPerHari = $aturan->nominal;
        $totalDenda = $nominalPerHari * $hariTerlambat;

        $detail = DetailDenda::create([
            'id_pengembalian'    => $idPengembalian,
            'id_alat'            => $idAlat,
            'id_aturan_denda'    => $aturan->id,
            'jenis_denda'        => 'keterlambatan',
            'jumlah_hari'        => $hariTerlambat,
            'nominal_per_satuan' => $nominalPerHari,
            'total_nominal'      => $totalDenda,
            'keterangan'         => "Terlambat {$hariTerlambat} hari",
        ]);

        return [
            'total'  => $totalDenda,
            'detail' => $detail,
        ];
    }

    // Hitung denda kerusakan
    private function hitungDendaKerusakan(int $idPengembalian, int $idAlat, string $kondisi, ?string $keterangan): array
    {
        $jenisDenda = $kondisi === 'rusak_ringan' ? 'rusak_sedang' : 'rusak_berat';
        $aturan = AturanDenda::getAktif($jenisDenda);
        
        if (!$aturan) {
            throw new \Exception("Aturan denda {$jenisDenda} tidak ditemukan", 500);
        }

        $totalDenda = $aturan->nominal;

        $detail = DetailDenda::create([
            'id_pengembalian'    => $idPengembalian,
            'id_alat'            => $idAlat,
            'id_aturan_denda'    => $aturan->id,
            'jenis_denda'        => $jenisDenda,
            'jumlah_hari'        => 0,
            'nominal_per_satuan' => $totalDenda,
            'total_nominal'      => $totalDenda,
            'keterangan'         => $keterangan ?? "Alat mengalami {$jenisDenda}",
        ]);

        return [
            'total'  => $totalDenda,
            'detail' => $detail,
        ];
    }

    // Htiung denda kehilangan kelengkapan
    private function hitungDendaKehilanganKelengkapan(int $idPengembalian, int $idAlat, string $namaKelengkapan): array
    {
        $aturan = AturanDenda::getAktif('kehilangan_kelengkapan');

        if (!$aturan) {
            throw new \Exception('Aturan denda kehilangan kelengkapan tidak ditemukan', 500);
        }

        $totalDenda = $aturan->nominal;

        $detail = DetailDenda::create([
            'id_pengembalian'    => $idPengembalian,
            'id_alat'            => $idAlat,
            'id_aturan_denda'    => $aturan->id,
            'jenis_denda'        => 'kehilangan_kelengkapan',
            'jumlah_hari'        => 0,
            'nominal_per_satuan' => $totalDenda,
            'total_nominal'      => $totalDenda,
            'keterangan'         => "Kehilangan kelengkapan: {$namaKelengkapan}",
        ]);

        return [
            'total'  => $totalDenda,
            'detail' => $detail,
        ];
    }
}