<?php

namespace App\Providers;

use App\Auth\FtpAccountProvider;
use App\Models\FtpAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Auth::provider('ftp_eloquent', function ($app, array $config) {
            return new FtpAccountProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $hasUsers = Schema::hasTable('users') && User::count() > 0;
        } catch (\Throwable) {
            $hasUsers = false;
        }

        View::share('hasUsers', $hasUsers);
    }
}
