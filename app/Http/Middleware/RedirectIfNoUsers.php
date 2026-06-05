<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RedirectIfNoUsers
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('register') || $request->routeIs('register', 'password.*', 'verification.*')) {
            return $next($request);
        }

        try {
            if (Schema::hasTable('users') && User::count() === 0) {
                return redirect()->route('register');
            }
        } catch (\Throwable) {
            //
        }

        return $next($request);
    }
}
