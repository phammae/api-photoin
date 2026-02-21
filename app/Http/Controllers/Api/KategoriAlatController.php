<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KategoriAlatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KategoriAlatController extends Controller
{
    public function __construct(
        private KategoriAlatService $kategoriService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $result = $this->kategoriService->getAll();
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->kategoriService->getById($id);
            return response()->json($result);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:50|unique:kategori_alat,nama_kategori',
                'deskripsi'     => 'nullable|string',
                'icon'          => 'nullable|string|max:100',
            ], [
                'nama_kategori.required' => 'Nama kategori wajib diisi',
                'nama_kategori.unique'   => 'Nama kategori sudah ada',
            ]);

            $result = $this->kategoriService->create($validated, $request->user()->id);

            return response()->json($result, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:50',
                'deskripsi'     => 'nullable|string',
                'icon'          => 'nullable|string|max:100',
            ]);

            $result = $this->kategoriService->update($id, $validated, $request->user()->id);

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

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->kategoriService->delete($id, $request->user()->id);
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