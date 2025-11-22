<?php

namespace App\Providers;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        Schema::defaultStringLength(191);

        // Đăng ký observer để tự động đồng bộ avatar (safe mode)
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
            HttpRequest::setTrustedProxies(['*'], HttpRequest::HEADER_X_FORWARDED_PROTO);
        }
    }
}
