<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\FtpAccount;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ftp_accounts', function (Blueprint $table) {
            $table->string('hashed_password')->nullable()->after('password');
        });

        FtpAccount::whereNotNull('password')->each(function (FtpAccount $ftp) {
            $ftp->updateQuietly(['hashed_password' => Hash::make($ftp->password)]);
        });
    }

    public function down(): void
    {
        Schema::table('ftp_accounts', function (Blueprint $table) {
            $table->dropColumn('hashed_password');
        });
    }
};
