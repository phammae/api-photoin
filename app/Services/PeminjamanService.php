<?php

namespace App\Services;

use App\Models\DetailPeminjaman;
use App\Models\HargaSewa;
use App\Models\LogAktivitas;
use App\Repositories\Contracts\AlatRepositoryInterface;
use App\Repositories\Contracts\PeminjamanRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PeminjamanService
{
    public function __construct(
        private PeminjamanRepositoryInterface $peminjamanRepository,
        private AlatRepositoryInterface $alatRepository
    ) {}

    /**
     * Get daftar peminjaman (sesuai role)
     */
    public function getAll(array $filters, int $perPage, string $role, int $userId): array
    {
        if ($role === 'penyewa') {
            // Penyewa hanya lihat peminjamannya sendiri
            $peminjaman = $this->peminjamanRepository->getByPenyewa($userId, $filters, $perPage);
        } else {
            // Admin & Petugas lihat semua
            $peminjaman = $this->peminjamanRepository->getAll($filters, $perPage);
        }

        return [
            'success' => true,
            'data'    => $peminjaman,
        ];
    }

    /**
     * Get detail peminjaman
     */
    public function getById(int $id, int $userId, string $role): array
    {
        $peminjaman = $this->peminjamanRepository->findById($id);

        if (!$peminjaman) {
            throw new \Exception('Peminjaman tidak ditemukan', 404);
        }

        // Penyewa hanya bisa lihat peminjamannya sendiri
        if ($role === 'penyewa' && $peminjaman->id_penyewa !== $userId) {
            throw new \Exception('Anda tidak memiliki akses ke peminjaman ini', 403);
        }

        return [
            'success' => true,
            'data'    => $peminjaman,
        ];
    }

    /**
     * Ajukan peminjaman baru (Penyewa)
     */
    public function create(array $data, int $userId): array
    {
        DB::beginTransaction();

        try {
            // Validasi: cek ketersediaan semua alat
            foreach ($data['alat'] as $item) {
                $isAvailable = $this->alatRepository->isAvailableOnDate(
                    $item['id_alat'],
                    $data['tanggal_pinjam'],
                    $data['tanggal_kembali_rencana']
                );

                if (!$isAvailable) {
                    $alat = $this->alatRepository->findById($item['id_alat']);
                    throw new \Exception(
                        "Alat {$alat->nama_alat} tidak tersedia pada tanggal tersebut",
                        400
                    );
                }
            }

            // Hitung total harga
            $totalHarga = 0;
            $detailsData = [];

            foreach ($data['alat'] as $item) {
                $hargaSewa = HargaSewa::where('id_alat', $item['id_alat'])
                                      ->where('durasi_hari', $item['durasi_hari'])
                                      ->where('is_active', true)
                                      ->first();

                if (!$hargaSewa) {
                    throw new \Exception(
                        "Harga sewa untuk durasi {$item['durasi_hari']} hari tidak ditemukan",
                        400
                    );
                }

                $subtotal = $hargaSewa->harga;
                $totalHarga += $subtotal;

                $detailsData[] = [
                    'id_alat'        => $item['id_alat'],
                    'id_harga_sewa'  => $hargaSewa->id,
                    'durasi_hari'    => $item['durasi_hari'],
                    'harga_per_hari' => $hargaSewa->hargaPerHari(),
                    'subtotal'       => $subtotal,
                    'kondisi_awal'   => 'baik',
                ];
            }

            // Buat peminjaman
            $peminjaman = $this->peminjamanRepository->create([
                'id_penyewa'              => $userId,
                'tanggal_pinjam'          => $data['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'],
                'total_harga'             => $totalHarga,
                'catatan_penyewa'         => $data['catatan'] ?? null,
                'status'                  => 'menunggu',
            ]);

            // Buat detail peminjaman
            foreach ($detailsData as $detail) {
                DetailPeminjaman::create(array_merge($detail, [
                    'id_peminjaman' => $peminjaman->id
                ]));
            }

            LogAktivitas::log(
                $userId,
                'CREATE_PEMINJAMAN',
                "Mengajukan peminjaman: {$peminjaman->kode_peminjaman}",
                'peminjaman',
                $peminjaman->id
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Peminjaman berhasil diajukan. Tunggu persetujuan dari petugas.',
                'data'    => $peminjaman->load(['details.alat']),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Setujui peminjaman (Petugas)
     */
    public function approve(int $id, int $petugasId, ?string $catatan = null): array
    {
        $peminjaman = $this->peminjamanRepository->findById($id);

        if (!$peminjaman) {
            throw new \Exception('Peminjaman tidak ditemukan', 404);
        }

        if (!$peminjaman->isMenunggu()) {
            throw new \Exception('Peminjaman tidak dalam status menunggu persetujuan', 400);
        }

        $this->peminjamanRepository->updateStatus($peminjaman, 'disetujui', [
            'id_petugas'       => $petugasId,
            'disetujui_oleh'   => $petugasId,
            'disetujui_at'     => now(),
            'catatan_petugas'  => $catatan,
        ]);

        LogAktivitas::log(
            $petugasId,
            'APPROVE_PEMINJAMAN',
            "Menyetujui peminjaman: {$peminjaman->kode_peminjaman}",
            'peminjaman',
            $peminjaman->id
        );

        return [
            'success' => true,
            'message' => 'Peminjaman berhasil disetujui',
            'data'    => $peminjaman->fresh(['details.alat', 'penyewa']),
        ];
    }

    /**
     * Tolak peminjaman (Petugas)
     */
    public function reject(int $id, int $petugasId, string $alasan): array
    {
        $peminjaman = $this->peminjamanRepository->findById($id);

        if (!$peminjaman) {
            throw new \Exception('Peminjaman tidak ditemukan', 404);
        }

        if (!$peminjaman->isMenunggu()) {
            throw new \Exception('Peminjaman tidak dalam status menunggu persetujuan', 400);
        }

        $this->peminjamanRepository->updateStatus($peminjaman, 'ditolak', [
            'id_petugas'      => $petugasId,
            'disetujui_oleh'  => $petugasId,
            'disetujui_at'    => now(),
            'alasan_tolak'    => $alasan,
        ]);

        LogAktivitas::log(
            $petugasId,
            'REJECT_PEMINJAMAN',
            "Menolak peminjaman: {$peminjaman->kode_peminjaman}",
            'peminjaman',
            $peminjaman->id
        );

        return [
            'success' => true,
            'message' => 'Peminjaman berhasil ditolak',
            'data'    => $peminjaman->fresh(),
        ];
    }

    /**
     * Batalkan peminjaman (Penyewa)
     */
    public function cancel(int $id, int $userId): array
    {
        $peminjaman = $this->peminjamanRepository->findById($id);

        if (!$peminjaman) {
            throw new \Exception('Peminjaman tidak ditemukan', 404);
        }

        if ($peminjaman->id_penyewa !== $userId) {
            throw new \Exception('Anda tidak memiliki akses untuk membatalkan peminjaman ini', 403);
        }

        if (!$peminjaman->isMenunggu() && !$peminjaman->isDisetujui()) {
            throw new \Exception('Peminjaman tidak dapat dibatalkan', 400);
        }

        $this->peminjamanRepository->updateStatus($peminjaman, 'dibatalkan');

        LogAktivitas::log(
            $userId,
            'CANCEL_PEMINJAMAN',
            "Membatalkan peminjaman: {$peminjaman->kode_peminjaman}",
            'peminjaman',
            $peminjaman->id
        );

        return [
            'success' => true,
            'message' => 'Peminjaman berhasil dibatalkan',
        ];
    }

    /**
     * Serahkan alat (update status jadi dipinjam) - Petugas
     */
    public function handover(int $id, int $petugasId): array
    {
        $peminjaman = $this->peminjamanRepository->findById($id);

        if (!$peminjaman) {
            throw new \Exception('Peminjaman tidak ditemukan', 404);
        }

        if (!$peminjaman->isDisetujui()) {
            throw new \Exception('Peminjaman belum disetujui', 400);
        }

        DB::beginTransaction();
        try {
            // Update status peminjaman
            $this->peminjamanRepository->updateStatus($peminjaman, 'dipinjam', [
                'id_petugas' => $petugasId,
            ]);

            // Update status semua alat jadi 'disewa'
            foreach ($peminjaman->details as $detail) {
                $this->alatRepository->updateStatus($detail->alat, 'disewa');
            }

            LogAktivitas::log(
                $petugasId,
                'HANDOVER_PEMINJAMAN',
                "Menyerahkan alat peminjaman: {$peminjaman->kode_peminjaman}",
                'peminjaman',
                $peminjaman->id
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Alat berhasil diserahkan',
                'data'    => $peminjaman->fresh(['details.alat']),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}