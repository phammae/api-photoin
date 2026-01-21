<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OAuthController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');
    
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])
        ->name('auth.verify-email');

    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])
        ->name('auth.resend-verificator');

    // Google OAuth
    Route::get('/google', [OAuthController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');
    
    Route::get('/google/callback', [OAuthController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me'])
        ->name('auth.me');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working',
        'timestamp' => now(),
    ]);
});
