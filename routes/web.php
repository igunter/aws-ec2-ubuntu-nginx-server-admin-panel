<?php

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$enableRegistration = false;

try {
    $enableRegistration = Schema::hasTable('users') ? User::count() === 0 : true;
} catch (\Throwable $e) {
    $enableRegistration = false;
}

Auth::routes(['register' => $enableRegistration]);

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);
