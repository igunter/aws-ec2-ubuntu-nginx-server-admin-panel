<?php

namespace App\Providers;

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
        if (!app()->runningInConsole() && request()->server('SERVER_ADDR')) {
            $scheme = request()->getScheme() ?: 'http';
            $host = request()->server('SERVER_ADDR');

            URL::forceRootUrl($scheme.'://'.$host);
            config(['app.url' => $scheme.'://'.$host]);
        }
    }
}
