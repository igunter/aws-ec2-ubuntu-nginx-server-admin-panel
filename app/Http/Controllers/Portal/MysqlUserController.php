<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MysqlDatabase;
use App\Models\MysqlUser;
use App\Services\MysqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MysqlUserController extends Controller
{
    private function account()
    {
        return Auth::user()->account;
    }

    private function authoriseDb(MysqlDatabase $db): void
    {
        abort_if($db->account_id !== $this->account()->id, 403);
    }

    private function authoriseUser(MysqlUser $user): void
    {
        abort_if($user->mysqlDatabase->account_id !== $this->account()->id, 403);
    }

    private function prefix(): string
    {
        return str_replace('-', '_', $this->account()->slug) . '_';
    }

    public function create(MysqlDatabase $mysqlDatabase)
    {
        $this->authoriseDb($mysqlDatabase);

        return view('portal.mysql-users.create', compact('mysqlDatabase'));
    }

    public function store(Request $request, MysqlDatabase $mysqlDatabase)
    {
        $this->authoriseDb($mysqlDatabase);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:28', 'regex:/^[a-z][a-z0-9_]*$/'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $fullUsername = $this->prefix() . $validated['username'];

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

        return redirect()->route('portal.mysql-databases.show', $mysqlDatabase)->with('success', "User {$fullUsername} created.");
    }

    public function edit(MysqlUser $mysqlUser)
    {
        $this->authoriseUser($mysqlUser);

        return view('portal.mysql-users.edit', compact('mysqlUser'));
    }

    public function update(Request $request, MysqlUser $mysqlUser)
    {
        $this->authoriseUser($mysqlUser);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        try {
            MysqlService::updateUserPassword($mysqlUser->username, $validated['password']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $mysqlUser->update(['password' => $validated['password']]);

        return redirect()->route('portal.mysql-databases.show', $mysqlUser->mysqlDatabase)
            ->with('success', "Password updated for {$mysqlUser->username}.");
    }

    public function destroy(MysqlUser $mysqlUser)
    {
        $this->authoriseUser($mysqlUser);
        $db = $mysqlUser->mysqlDatabase;

        try {
            MysqlService::dropUser($mysqlUser->username);
        } catch (\RuntimeException $e) {
            return redirect()->route('portal.mysql-databases.show', $db)->with('error', $e->getMessage());
        }

        $mysqlUser->delete();

        return redirect()->route('portal.mysql-databases.show', $db)->with('success', "User {$mysqlUser->username} deleted.");
    }
}
