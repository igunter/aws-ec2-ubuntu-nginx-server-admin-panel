<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

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
