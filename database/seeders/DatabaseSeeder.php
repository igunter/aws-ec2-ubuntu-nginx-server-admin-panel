<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FtpAccount;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name'     => 'Ian Gunter',
            'email'    => 'ianwgunter@gmail.com',
            'password' => bcrypt('P4$$w0rd'),
        ]);

        $account = Account::create([
            'domain' => 'autoparel.co.uk',
            'slug'   => 'autoparel-co-uk',
            'email'  => 'portal@autoparel.co.uk',
        ]);

        $passwordHash = bcrypt('P4$$w0rd');

        FtpAccount::create([
            'account_id'     => $account->id,
            'username'       => 'root@autoparel.co.uk',
            'password'       => $passwordHash,
            'root_directory' => '/public',
            'is_active'      => true,
        ]);

        User::create([
            'name'       => 'autoparel.co.uk',
            'email'      => 'portal@autoparel.co.uk',
            'password'   => $passwordHash,
            'account_id' => $account->id,
        ]);
    }
}
