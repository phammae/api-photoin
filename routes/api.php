<?php

use App\Http\Controllers\Api\AlatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriAlatController;
use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\PeminjamanController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// PUBLIC ROUTES (tanpa auth)
// ─────────────────────────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('auth.resend-verification');

    // Google OAuth
    Route::get('/google', [OAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/google/callback', [OAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working',
        'timestamp' => now(),
    ]);
});

// ✅ Endpoint publik untuk lihat alat & kategori (tanpa auth)
Route::get('/alat', [AlatController::class, 'index'])->name('alat.index.public');
Route::get('/alat/available', [AlatController::class, 'available'])->name('alat.available.public');
Route::get('/alat/{id}', [AlatController::class, 'show'])->name('alat.show.public');
Route::get('/kategori', [KategoriAlatController::class, 'index'])->name('kategori.index.public');
Route::get('/kategori/{id}', [KategoriAlatController::class, 'show'])->name('kategori.show.public');

// ─────────────────────────────────────────────────────────────────────────────
// AUTHENTICATED ROUTES
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // ─── Alat (Admin untuk CRUD) ──────────────────────────────────────────
    Route::prefix('alat')->middleware('role:admin')->group(function () {
        Route::post('/', [AlatController::class, 'store'])->name('alat.store');
        Route::put('/{id}', [AlatController::class, 'update'])->name('alat.update');
        Route::delete('/{id}', [AlatController::class, 'destroy'])->name('alat.destroy');
    });

    // Cek availability (authenticated users)
    Route::post('/alat/{id}/check-availability', [AlatController::class, 'checkAvailability'])
         ->name('alat.check-availability');

    // ─── Kategori (Admin untuk CRUD) ──────────────────────────────────────
    Route::prefix('kategori')->middleware('role:admin')->group(function () {
        Route::post('/', [KategoriAlatController::class, 'store'])->name('kategori.store');
        Route::put('/{id}', [KategoriAlatController::class, 'update'])->name('kategori.update');
        Route::delete('/{id}', [KategoriAlatController::class, 'destroy'])->name('kategori.destroy');
    });

    // ─── Peminjaman ───────────────────────────────────────────────────────
    Route::prefix('peminjaman')->group(function () {
        Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/{id}', [PeminjamanController::class, 'show'])->name('peminjaman.show');

        // Penyewa
        Route::middleware('role:penyewa')->group(function () {
            Route::post('/', [PeminjamanController::class, 'store'])->name('peminjaman.store');
            Route::post('/{id}/cancel', [PeminjamanController::class, 'cancel'])->name('peminjaman.cancel');
        });

        // Petugas & Admin
        Route::middleware('role:petugas,admin')->group(function () {
            Route::post('/{id}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve');
            Route::post('/{id}/reject', [PeminjamanController::class, 'reject'])->name('peminjaman.reject');
            Route::post('/{id}/handover', [PeminjamanController::class, 'handover'])->name('peminjaman.handover');
        });
    });
});