<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\MysqlDatabase;
use App\Models\MysqlUser;
use App\Services\MysqlService;
use Illuminate\Http\Request;

class MysqlDatabaseController extends Controller
{
    private static function dbPrefix(Account $account): string
    {
        return str_replace('-', '_', $account->slug) . '_';
    }

    public function index()
    {
        $databases = MysqlDatabase::with('account')->orderBy('name')->get();

        return view('mysql-databases.index', compact('databases'));
    }

    public function create()
    {
        $accounts = Account::orderBy('domain')->get();

        return view('mysql-databases.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'name'       => ['required', 'string', 'max:48', 'regex:/^[a-z][a-z0-9_]*$/'],
        ]);

        $account  = Account::find($validated['account_id']);
        $fullName = static::dbPrefix($account) . $validated['name'];

        if (MysqlDatabase::where('name', $fullName)->exists()) {
            return back()->withInput()->withErrors(['name' => 'This database name already exists.']);
        }

        try {
            MysqlService::createDatabase($fullName);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        MysqlDatabase::create([
            'account_id' => $validated['account_id'],
            'name'       => $fullName,
        ]);

        return redirect()->route('mysql-databases.index')->with('success', "Database {$fullName} created.");
    }

    public function show(MysqlDatabase $mysqlDatabase)
    {
        $mysqlDatabase->load('mysqlUsers', 'account');

        return view('mysql-databases.show', compact('mysqlDatabase'));
    }

    public function storeUser(Request $request, MysqlDatabase $mysqlDatabase)
    {
        $account = $mysqlDatabase->account;
        $prefix  = static::dbPrefix($account);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:28', 'regex:/^[a-z][a-z0-9_]*$/'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $fullUsername = $prefix . $validated['username'];

        if (MysqlUser::where('username', $fullUsername)->exists()) {
            return back()->withInput()->withErrors(['username' => 'This username already exists.']);
        }

        try {
            MysqlService::createUser($fullUsername, $validated['password'], $mysqlDatabase->name);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        MysqlUser::create([
            'mysql_database_id' => $mysqlDatabase->id,
            'username'          => $fullUsername,
            'password'          => $validated['password'],
        ]);

        return redirect()->route('mysql-databases.show', $mysqlDatabase)->with('success', "User {$fullUsername} created.");
    }

    public function destroyUser(MysqlDatabase $mysqlDatabase, MysqlUser $mysqlUser)
    {
        try {
            MysqlService::dropUser($mysqlUser->username);
        } catch (\RuntimeException $e) {
            return redirect()->route('mysql-databases.show', $mysqlDatabase)->with('error', $e->getMessage());
        }

        $mysqlUser->delete();

        return redirect()->route('mysql-databases.show', $mysqlDatabase)->with('success', "User {$mysqlUser->username} deleted.");
    }

    public function destroy(MysqlDatabase $mysqlDatabase)
    {
        foreach ($mysqlDatabase->mysqlUsers as $user) {
            try {
                MysqlService::dropUser($user->username);
            } catch (\RuntimeException) {}
        }

        try {
            MysqlService::dropDatabase($mysqlDatabase->name);
        } catch (\RuntimeException $e) {
            return redirect()->route('mysql-databases.index')->with('error', $e->getMessage());
        }

        $mysqlDatabase->delete();

        return redirect()->route('mysql-databases.index')->with('success', "Database {$mysqlDatabase->name} deleted.");
    }
}
