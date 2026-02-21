<?php

namespace App\Providers;

use App\Repositories\AlatRepository;
use App\Repositories\Contracts\AlatRepositoryInterface;
use App\Repositories\Contracts\KategoriAlatRepositoryInterface;
use App\Repositories\Contracts\PeminjamanRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\KategoriAlatRepository;
use App\Repositories\PeminjamanRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding Repository Interface ke Implementasi
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AlatRepositoryInterface::class, AlatRepository::class);
        $this->app->bind(KategoriAlatRepositoryInterface::class, KategoriAlatRepository::class);
        $this->app->bind(PeminjamanRepositoryInterface::class, PeminjamanRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}