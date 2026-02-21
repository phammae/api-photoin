<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PeminjamanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\TryCatch;

class PeminjamanController extends Controller
{
    public function __construct(
        private PeminjamanService $peminjamanService
    ) {}

    // Get /api/peminjaman untuk melihat list peminjaman

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status'         => $request->query('status'),
                'tanggal_dari'   => $request->query('tanggal_dari'),
                'tanggal_sampai' => $request->query('tanggal_sampai'),
                'search'         => $request->query('search'),
            ];

            $perPage = (int) $request->query('per_page', 15);
            $user = $request->user();

            $result = $this->peminjamanService->getAll($filters, $perPage, $user->role, $user->id);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Get /api/peminjaman/{id} melihat detail peminjaman

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->peminjamanService->getById($id, $user->id, $user->role);

            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/peminjaman Mengajukan Peminjaman (penyewa)

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tanggal_pinjam'          => 'required|date|after_or_equal:today',
                'tanggal_kembali_rencana' => 'required|date|after:tanggal_pinjam',
                'alat'                    => 'required|array|min:1',
                'alat.*.id_alat'          => 'required|exists:alat,id',
                'alat.*.durasi_hari'      => 'required|integer|min:1',
                'catatan'                 => 'nullable|string',
            ], [
                'tanggal_pinjam.required'          => 'Tanggal pinjam wajib diisi',
                'tanggal_pinjam.after_or_equal'    => 'Tanggal pinjam tidak boleh kurang dari hari ini',
                'tanggal_kembali_rencana.required' => 'Tanggal kembali wajib diisi',
                'tanggal_kembali_rencana.after'    => 'Tanggal kembali harus setelah tanggal pinjam',
                'alat.required'                    => 'Pilih minimal 1 alat',
                'alat.*.id_alat.required'          => 'ID alat wajib diisi',
                'alat.*.id_alat.exists'            => 'Alat tidak ditemukan',
                'alat.*.durasi_hari.required'      => 'Durasi hari wajib diisi',
            ]);

            $result = $this->peminjamanService->create($validated, $request->user()->id);

            return response()->json($result, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/peminjaman/{id}/approve Peminjaman disetujui petugas

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'catatan' => 'nullable|string',
            ]);

            $result = $this->peminjamanService->approve(
                $id,
                $request->user()->id,
                $validated['catatan'] ?? null
            );

            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/peminjaman/{id}/reject Peminjaman ditolak petugas

     public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'alasan' => 'required|string',
            ], [
                'alasan.required' => 'Alasan penolakan wajib diisi',
            ]);

            $result = $this->peminjamanService->reject(
                $id,
                $request->user()->id,
                $validated['alasan']
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/peminjaman/{id}/cancel Peminjaman dibatalkan penyewa

    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->peminjamanService->cancel($id, $request->user()->id);
            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/peminjaman/{id}/handover Penyerahan alat petugas

    public function handover(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->peminjamanService->handover($id, $request->user()->id);
            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }
}