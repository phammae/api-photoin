<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AlatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AlatController extends Controller
{
    public function __construct(
        private AlatService $alatService
    ) {}

    // Get /api/alat  List alat dengan filter & pagination
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'id_kategori' => $request->query('kategori'),
                'status'      => $request->query('status'),
                'kondisi'     => $request->query('kondisi'),
                'search'      => $request->query('search'),
            ];

            $perPage = (int) $request->query('per_page', 15);

            $result = $this->alatService->getAll($filters, $perPage);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Get /api/alat/available List alat yang tersedia
    public function available(): JsonResponse
    {
        try {
            $result = $this->alatService->getAvailable();
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Get /api/alat/{id} Detail alat
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->alatService->getById($id);
            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/alat Tambah alat baru (Admin only)
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'kode_alat'          => 'nullable|string|max:20|unique:alat,kode_alat',
                'nama_alat'          => 'required|string|max:100',
                'id_kategori'        => 'required|exists:kategori_alat,id',
                'merk'               => 'required|string|max:50',
                'tipe'               => 'nullable|string|max:50',
                'serial_number'      => 'nullable|string|max:50|unique:alat,serial_number',
                'tahun_pembelian'    => 'nullable|integer|min:1900|max:' . date('Y'),
                'harga_beli'         => 'nullable|numeric|min:0',
                'kondisi'            => 'required|in:baik,rusak_ringan,rusak_berat',
                'status'             => 'required|in:tersedia,disewa,maintenance,rusak',
                'foto'               => 'nullable|array',
                'spesifikasi'        => 'nullable|string',
                'lokasi_penyimpanan' => 'nullable|string|max:100',
            ], [
                'nama_alat.required'   => 'Nama alat wajib diisi',
                'id_kategori.required' => 'Kategori wajib dipilih',
                'id_kategori.exists'   => 'Kategori tidak ditemukan',
                'merk.required'        => 'Merk wajib diisi',
                'kondisi.required'     => 'Kondisi wajib dipilih',
                'status.required'      => 'Status wajib dipilih',
            ]);

            $result = $this->alatService->create($validated, $request->user()->id);

            return response()->json($result, 201);

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

    // Put /api/alat/{id} Update alat (Admin only)
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'kode_alat'          => 'nullable|string|max:20',
                'nama_alat'          => 'required|string|max:100',
                'id_kategori'        => 'required|exists:kategori_alat,id',
                'merk'               => 'required|string|max:50',
                'tipe'               => 'nullable|string|max:50',
                'serial_number'      => 'nullable|string|max:50',
                'tahun_pembelian'    => 'nullable|integer|min:1900|max:' . date('Y'),
                'harga_beli'         => 'nullable|numeric|min:0',
                'kondisi'            => 'required|in:baik,rusak_ringan,rusak_berat',
                'status'             => 'required|in:tersedia,disewa,maintenance,rusak',
                'foto'               => 'nullable|array',
                'spesifikasi'        => 'nullable|string',
                'lokasi_penyimpanan' => 'nullable|string|max:100',
            ]);

            $result = $this->alatService->update($id, $validated, $request->user()->id);

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

    // Delete /api/alat/{id} Hapus alat (Admin only)
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->alatService->delete($id, $request->user()->id);
            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // Post /api/alat/{id}/check-availability Cek ketersediaan
    public function checkAvailability(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tanggal_pinjam'        => 'required|date|after_or_equal:today',
                'tanggal_kembali' => 'required|date|after:tanggal_pinjam',
            ]);

            $result = $this->alatService->checkAvailability(
                $id,
                $validated['tanggal_pinjam'],
                $validated['tanggal_kembali']
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
}