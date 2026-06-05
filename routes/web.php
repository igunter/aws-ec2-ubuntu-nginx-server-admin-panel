<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

try {
    $enableLogin        = Schema::hasTable('users')  ?User::count() > 0 : false;
    $enableRegistration = Schema::hasTable('users')  ?User::count() === 0 : true;
} catch (\Throwable $e) {
    $enableLogin        = true;
    $enableRegistration = false;
}

Auth::routes([
    'register' => $enableRegistration,
    'login'    => true,
]);

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('home', [HomeController::class, 'index']);

    Route::post('accounts/{account}/suspend', [AccountController::class, 'suspend'])->name('accounts.suspend');
    Route::patch('accounts/{account}/ssl', [AccountController::class, 'toggleSsl'])->name('accounts.ssl.toggle');
    Route::resource('accounts', AccountController::class);

    Route::get('git/pull', [GitController::class, 'show'])->name('git.pull.show');
    Route::post('git/pull', [GitController::class, 'pull'])->name('git.pull');
});

