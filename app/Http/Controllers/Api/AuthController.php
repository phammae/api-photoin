<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    // register
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'username'     => 'required|string|max:50|unique:users,username',
                'email'        => 'required|email|max:100|unique:users,email',
                'password'     => 'required|string|min:8|confirmed',
                'nama_lengkap' => 'required|string|max:100',
                'no_hp'        => 'nullable|string|max:15',
                'alamat'       => 'nullable|string',
            ], [
                'username.required'      => 'Username wajib diisi',
                'username.unique'        => 'Username sudah digunakan',
                'email.required'         => 'Email wajib diisi',
                'email.email'            => 'Format email tidak valid',
                'email.unique'           => 'Email sudah terdaftar',
                'password.required'      => 'Password wajib diisi',
                'password.min'           => 'Password minimal 8 karakter',
                'password.confirmed'     => 'Konfirmasi password tidak cocok',
                'nama_lengkap.required'  => 'Nama lengkap wajib diisi',
            ]);

            $result = $this->authService->register($validated);

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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // login
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'login'    => 'required|string',
                'password' => 'required|string',
            ], [
                'login.required'    => 'Username atau email wajib diisi',
                'password.required' => 'Password wajib diisi',
            ]);

            $result = $this->authService->login($validated);
            $statusCode = $result['status_code'] ?? 200;
            unset($result['status_code']);

            return response()->json($result, $statusCode);

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

    // verification email
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $token = $request->query('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token verifikasi tidak ditemukan',
                ], 400);
            }

            $result = $this->authService->verifyEmail($token);
            $statusCode = $result['status_code'] ?? 200;
            unset($result['status_code']);

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // kirim ulang verification email
    public function resendVerification(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            $result = $this->authService->resendVerification($validated['email']);
            $statusCode = $result['status_code'] ?? 200;
            unset($result['status_code']);

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_numeric($code) ? $code : 500);
        }
    }

    // get user
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }

    // logout
    public function logout(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->logout($request->user());
            $statusCode = $result['status_code'] ?? 200;
            unset($result['status_code']);

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}