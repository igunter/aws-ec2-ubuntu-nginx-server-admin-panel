<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FtpAccount;
use App\Services\FtpService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FtpAccountController extends Controller
{
    public function index()
    {
        $ftpAccounts = FtpAccount::with('account')->orderBy('username')->get();

        return view('ftp-accounts.index', compact('ftpAccounts'));
    }

    public function create()
    {
        $accounts = Account::orderBy('domain')->get();

        return view('ftp-accounts.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'       => ['required', 'string', 'max:255', Rule::unique('ftp_accounts', 'username')->where('account_id', $request->account_id)],
            'account_id'     => ['required', 'exists:accounts,id'],
            'password'       => ['required', 'string', 'min:8'],
            'root_directory' => ['required', 'string', 'max:255'],
        ]);

        $account  = Account::find($validated['account_id']);
        $username = $validated['username'] . '@' . $account->domain;
        $ftpRoot  = '/var/www/' . $account->slug . $validated['root_directory'];

        $passwordHash = bcrypt($validated['password']);

        try {
            FtpService::provision($username, $passwordHash, $ftpRoot);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        FtpAccount::create([
            'account_id'     => $validated['account_id'],
            'username'       => $username,
            'password'       => $passwordHash,
            'root_directory' => $validated['root_directory'],
            'is_active'      => true,
        ]);

        return redirect()->route('ftp-accounts.index')->with('success', 'FTP account created successfully.');
    }

    public function show(FtpAccount $ftpAccount)
    {
        //
    }

    public function edit(FtpAccount $ftpAccount)
    {
        return view('ftp-accounts.edit', compact('ftpAccount'));
    }

    public function update(Request $request, FtpAccount $ftpAccount)
    {
        $validated = $request->validate([
            'password'       => ['nullable', 'string', 'min:8'],
            'root_directory' => ['required', 'string', 'max:255'],
            'is_active'      => ['boolean'],
        ]);

        $isActive        = $request->boolean('is_active');
        $passwordChanged = ! empty($validated['password']);
        $rootChanged     = $validated['root_directory'] !== $ftpAccount->root_directory;
        $deactivating    = ! $isActive && $ftpAccount->is_active;
        $activating      = $isActive && ! $ftpAccount->is_active;
        $newPasswordHash = $passwordChanged ? bcrypt($validated['password']) : $ftpAccount->password;

        try {
            if ($deactivating) {
                FtpService::deprovision($ftpAccount->username);
            } elseif ($isActive && ($activating || $passwordChanged || $rootChanged)) {
                $account = $ftpAccount->account;
                $ftpRoot = '/var/www/' . $account->slug . $validated['root_directory'];
                FtpService::provision($ftpAccount->username, $newPasswordHash, $ftpRoot);
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $ftpAccount->update([
            'password'       => $newPasswordHash,
            'root_directory' => $validated['root_directory'],
            'is_active'      => $isActive,
        ]);

        return redirect()->route('ftp-accounts.index')->with('success', 'FTP account updated successfully.');
    }

    public function destroy(FtpAccount $ftpAccount)
    {
        try {
            FtpService::deprovision($ftpAccount->username);
        } catch (\RuntimeException $e) {
            return redirect()->route('ftp-accounts.index')->with('error', $e->getMessage());
        }

        $ftpAccount->delete();

        return redirect()->route('ftp-accounts.index')->with('success', 'FTP account deleted successfully.');
    }

    public function suspend(FtpAccount $ftpAccount)
    {
        $activating = ! $ftpAccount->is_active;
        $account    = $ftpAccount->account;
        $ftpRoot    = '/var/www/' . $account->slug . $ftpAccount->root_directory;

        try {
            if ($activating) {
                FtpService::provision($ftpAccount->username, $ftpAccount->password, $ftpRoot);
            } else {
                FtpService::deprovision($ftpAccount->username);
            }
        } catch (\RuntimeException $e) {
            return redirect()->route('ftp-accounts.index')->with('error', $e->getMessage());
        }

        $ftpAccount->is_active = $activating;
        $ftpAccount->save();

        $label = $activating ? 'activated' : 'suspended';
        return redirect()->route('ftp-accounts.index')->with('success', "FTP account {$ftpAccount->username} {$label}.");
    }

    public function directories(Request $request)
    {
        $account = Account::findOrFail($request->account_id);
        $webRoot = '/var/www/' . $account->slug;

        $dirs = ['/'];

        if (is_dir($webRoot)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($webRoot, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            $iterator->setMaxDepth(2);

            foreach ($iterator as $path) {
                if ($path->isDir()) {
                    $dirs[] = '/' . str_replace($webRoot . '/', '', $path->getPathname());
                }
            }

            sort($dirs);
        }

        return response()->json($dirs);
    }
}
