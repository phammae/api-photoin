<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PengembalianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PengembalianController extends Controller
{
    public function __construct(
        private PengembalianService $pengembalianService
    ) {}

    // Get /api/pengembalian List pengembalian untuk admin dan petugas
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'tanggal_dari'   => $request->query('tanggal_dari'),
                'tanggal_sampai' => $request->query('tanggal_sampai'),
                'ada_denda'      => $request->query('ada_denda'),
                'search'         => $request->query('search'),
            ];

            $perPage = (int) $request->query('per_page', 15);

            $result = $this->pengembalianService->getAll($filters, $perPage);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Get /api/pengembalian/{id} berisi Detail Pengembalian
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->pengembalianService->getById($id);
            return response()->json($result);

        } catch (\Exception $e) {
            $code = $this->getValidHttpCode($e->getCode());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    // Post /api/pengembalian Proses pengembalian admin dan petugas
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_peminjaman'   => 'required|exists:peminjaman,id',
                'tanggal_kembali' => 'nullable|date',
                'catatan'         => 'nullable|string',
                'alat'            => 'required|array|min:1',
                'alat.*.id_alat'  => 'required|exists:alat,id',
                'alat.*.kondisi'  => 'required|in:baik,kerusakan_ringan,kerusakan_berat',
                'alat.*.keterangan' => 'nullable|string',
                'alat.*.kelengkapan_hilang' => 'nullable|array',
            ], [
                'id_peminjaman.required' => 'ID peminjaman wajib diisi',
                'id_peminjaman.exists'   => 'Peminjaman tidak ditemukan',
                'alat.required'          => 'Data kondisi alat wajib diisi',
                'alat.*.id_alat.required' => 'ID alat wajib diisi',
                'alat.*.kondisi.required' => 'Kondisi alat wajib dipilih',
                'alat.*.kondisi.in'       => 'Kondisi harus: baik, kerusakan_ringan, atau kerusakan_berat',
            ]);

            // Transform array ke format yang diharapkan Service
            $transformedData = [
                'tanggal_kembali' => $validated['tanggal_kembali'] ?? null,
                'catatan'         => $validated['catatan'] ?? null,
                'alat'            => [],
            ];

            // Convert array format ke object dengan key ID alat
            foreach ($validated['alat'] as $item) {
                $transformedData['alat'][$item['id_alat']] = [
                    'kondisi'             => $item['kondisi'],
                    'keterangan'          => $item['keterangan'] ?? null,
                    'kelengkapan_hilang'  => $item['kelengkapan_hilang'] ?? [],
                ];
            }

            $result = $this->pengembalianService->process(
                $validated['id_peminjaman'],
                $transformedData,
                $request->user()->id
            );

            return response()->json($result, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $code = $this->getValidHttpCode($e->getCode());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    private function getValidHttpCode($code): int
    {
        return (is_numeric($code) && $code >= 100 && $code < 600) ? (int) $code : 500;
    }
}