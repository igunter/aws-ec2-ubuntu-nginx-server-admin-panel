<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MysqlDatabase;
use App\Services\MysqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MysqlDatabaseController extends Controller
{
    private function account()
    {
        return Auth::user()->account;
    }

    private function authorise(MysqlDatabase $db): void
    {
        abort_if($db->account_id !== $this->account()->id, 403);
    }

    private function prefix(): string
    {
        return str_replace('-', '_', $this->account()->slug) . '_';
    }

    public function create()
    {
        return view('portal.mysql-databases.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:48', 'regex:/^[a-z][a-z0-9_]*$/'],
        ]);

        $fullName = $this->prefix() . $validated['name'];

        if (MysqlDatabase::where('name', $fullName)->exists()) {
            return back()->withInput()->withErrors(['name' => 'This database name already exists.']);
        }

        try {
            MysqlService::createDatabase($fullName);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $db = MysqlDatabase::create([
            'account_id' => $this->account()->id,
            'name'       => $fullName,
        ]);

        return redirect()->route('portal.mysql-databases.show', $db)->with('success', "Database {$fullName} created.");
    }

    public function show(MysqlDatabase $mysqlDatabase)
    {
        $this->authorise($mysqlDatabase);
        $mysqlDatabase->load('mysqlUsers');

        return view('portal.mysql-databases.show', compact('mysqlDatabase'));
    }

    public function destroy(MysqlDatabase $mysqlDatabase)
    {
        $this->authorise($mysqlDatabase);

        foreach ($mysqlDatabase->mysqlUsers as $user) {
            try {
                MysqlService::dropUser($user->username);
            } catch (\RuntimeException) {}
        }

        try {
            MysqlService::dropDatabase($mysqlDatabase->name);
        } catch (\RuntimeException $e) {
            return redirect()->route('portal.dashboard')->with('error', $e->getMessage());
        }

        $mysqlDatabase->delete();

        return redirect()->route('portal.dashboard')->with('success', "Database {$mysqlDatabase->name} deleted.");
    }
}
