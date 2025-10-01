<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký observer để tự động đồng bộ avatar (safe mode)
        \App\Models\User::observe(\App\Observers\UserObserver::class);
    }
}
