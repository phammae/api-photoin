<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    //Handle Google OAuth Callback

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $result = $this->authService->handleGoogleCallback($googleUser);

            $frontendUrl  = config('app.frontend_url', 'http://localhost:3000');
            $isNewParam   = $result['is_new'] ? 'true' : 'false';
            $redirectUrl  = "{$frontendUrl}/auth/callback?token={$result['token']}&is_new={$isNewParam}";

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            $frontendUrl  = config('app.frontend_url', 'http://localhost:3000');
            $errorMessage = urlencode('Google OAuth gagal: ' . $e->getMessage());
            return redirect("{$frontendUrl}/login?error={$errorMessage}");
        }
    }
}