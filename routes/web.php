<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\FtpAccountController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MysqlDatabaseController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\UserController;
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

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware(['auth', 'portal'])->group(function () {
        Route::post('logout', [Portal\LoginController::class, 'logout'])->name('logout');
        Route::get('/', [Portal\DashboardController::class, 'index'])->name('dashboard');

        Route::get('ftp-accounts/create', [Portal\FtpAccountController::class, 'create'])->name('ftp-accounts.create');
        Route::post('ftp-accounts', [Portal\FtpAccountController::class, 'store'])->name('ftp-accounts.store');
        Route::get('ftp-accounts/{ftpAccount}/edit', [Portal\FtpAccountController::class, 'edit'])->name('ftp-accounts.edit');
        Route::patch('ftp-accounts/{ftpAccount}', [Portal\FtpAccountController::class, 'update'])->name('ftp-accounts.update');
        Route::delete('ftp-accounts/{ftpAccount}', [Portal\FtpAccountController::class, 'destroy'])->name('ftp-accounts.destroy');
        Route::patch('ftp-accounts/{ftpAccount}/suspend', [Portal\FtpAccountController::class, 'suspend'])->name('ftp-accounts.suspend');

        Route::get('mysql-databases/create', [Portal\MysqlDatabaseController::class, 'create'])->name('mysql-databases.create');
        Route::post('mysql-databases', [Portal\MysqlDatabaseController::class, 'store'])->name('mysql-databases.store');
        Route::get('mysql-databases/{mysqlDatabase}', [Portal\MysqlDatabaseController::class, 'show'])->name('mysql-databases.show');
        Route::delete('mysql-databases/{mysqlDatabase}', [Portal\MysqlDatabaseController::class, 'destroy'])->name('mysql-databases.destroy');

        Route::get('mysql-databases/{mysqlDatabase}/users/create', [Portal\MysqlUserController::class, 'create'])->name('mysql-databases.users.create');
        Route::post('mysql-databases/{mysqlDatabase}/users', [Portal\MysqlUserController::class, 'store'])->name('mysql-databases.users.store');
        Route::get('mysql-users/{mysqlUser}/edit', [Portal\MysqlUserController::class, 'edit'])->name('mysql-users.edit');
        Route::patch('mysql-users/{mysqlUser}', [Portal\MysqlUserController::class, 'update'])->name('mysql-users.update');
        Route::delete('mysql-users/{mysqlUser}', [Portal\MysqlUserController::class, 'destroy'])->name('mysql-users.destroy');
    });
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('home', [HomeController::class, 'index']);

    Route::patch('accounts/{account}/suspend', [AccountController::class, 'suspend'])->name('accounts.suspend');
    Route::patch('accounts/{account}/ssl', [AccountController::class, 'toggleSsl'])->name('accounts.ssl.toggle');
    Route::resource('accounts', AccountController::class);
    
    Route::get('ftp-accounts/directories', [FtpAccountController::class, 'directories'])->name('ftp-accounts.directories');
    Route::patch('ftp-accounts/{ftpAccount}/suspend', [FtpAccountController::class, 'suspend'])->name('ftp-accounts.suspend');
    Route::resource('ftp-accounts', FtpAccountController::class);

    Route::resource('users', UserController::class)->except(['show']);

    Route::post('mysql-databases/{mysqlDatabase}/users', [MysqlDatabaseController::class, 'storeUser'])->name('mysql-databases.users.store');
    Route::delete('mysql-databases/{mysqlDatabase}/users/{mysqlUser}', [MysqlDatabaseController::class, 'destroyUser'])->name('mysql-databases.users.destroy');
    Route::resource('mysql-databases', MysqlDatabaseController::class)->except(['edit', 'update']);

    Route::get('git/pull', [GitController::class, 'show'])->name('git.pull.show');
    Route::post('git/pull', [GitController::class, 'pull'])->name('git.pull');
});
