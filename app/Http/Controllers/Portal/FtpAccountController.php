<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FtpAccount;
use App\Services\FtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FtpAccountController extends Controller
{
    private function account()
    {
        return Auth::user()->account;
    }

    private function authorise(FtpAccount $ftpAccount): void
    {
        abort_if($ftpAccount->account_id !== $this->account()->id, 403);
    }

    public function create()
    {
        $account = $this->account();

        return view('portal.ftp-accounts.create', compact('account'));
    }

    public function store(Request $request)
    {
        $account = $this->account();

        $fullUsername = $request->input('username') . '@' . $account->domain;

        $validated = $request->validate([
            'username'       => ['required', 'string', 'max:255'],
            'password'       => ['required', 'string', 'min:8'],
            'root_directory' => ['required', 'in:/, /public'],
        ]);

        if (FtpAccount::where('username', $fullUsername)->exists()) {
            return back()->withInput()->withErrors(['username' => 'This FTP username already exists.']);
        }

        $username     = $fullUsername;
        $ftpRoot      = '/var/www/' . $account->slug . $validated['root_directory'];
        $passwordHash = bcrypt($validated['password']);

        try {
            FtpService::provision($username, $passwordHash, $ftpRoot);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        FtpAccount::create([
            'account_id'     => $account->id,
            'username'       => $username,
            'password'       => $passwordHash,
            'root_directory' => $validated['root_directory'],
            'is_active'      => true,
        ]);

        return redirect()->route('portal.dashboard')->with('success', 'FTP account created successfully.');
    }

    public function edit(FtpAccount $ftpAccount)
    {
        $this->authorise($ftpAccount);

        $account = $this->account();

        return view('portal.ftp-accounts.edit', compact('ftpAccount', 'account'));
    }

    public function update(Request $request, FtpAccount $ftpAccount)
    {
        $this->authorise($ftpAccount);

        $validated = $request->validate([
            'password'       => ['nullable', 'string', 'min:8'],
            'root_directory' => ['required', 'in:/, /public'],
            'is_active'      => ['boolean'],
        ]);

        $isActive        = $request->boolean('is_active');
        $passwordChanged = ! empty($validated['password']);
        $rootChanged     = $validated['root_directory'] !== $ftpAccount->root_directory;
        $deactivating    = ! $isActive && $ftpAccount->is_active;
        $activating      = $isActive && ! $ftpAccount->is_active;
        $newPassword     = $passwordChanged ? bcrypt($validated['password']) : $ftpAccount->password;

        try {
            if ($deactivating) {
                FtpService::deprovision($ftpAccount->username);
            } elseif ($isActive && ($activating || $passwordChanged || $rootChanged)) {
                $account = $this->account();
                $ftpRoot = '/var/www/' . $account->slug . $validated['root_directory'];
                FtpService::provision($ftpAccount->username, $newPassword, $ftpRoot);
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $ftpAccount->update([
            'password'       => $newPassword,
            'root_directory' => $validated['root_directory'],
            'is_active'      => $isActive,
        ]);

        return redirect()->route('portal.dashboard')->with('success', 'FTP account updated successfully.');
    }

    public function destroy(FtpAccount $ftpAccount)
    {
        $this->authorise($ftpAccount);

        try {
            FtpService::deprovision($ftpAccount->username);
        } catch (\RuntimeException $e) {
            return redirect()->route('portal.dashboard')->with('error', $e->getMessage());
        }

        $ftpAccount->delete();

        return redirect()->route('portal.dashboard')->with('success', 'FTP account deleted successfully.');
    }

    public function suspend(FtpAccount $ftpAccount)
    {
        $this->authorise($ftpAccount);

        $activating = ! $ftpAccount->is_active;
        $account    = $this->account();
        $ftpRoot    = '/var/www/' . $account->slug . $ftpAccount->root_directory;

        try {
            if ($activating) {
                FtpService::provision($ftpAccount->username, $ftpAccount->password, $ftpRoot);
            } else {
                FtpService::deprovision($ftpAccount->username);
            }
        } catch (\RuntimeException $e) {
            return redirect()->route('portal.dashboard')->with('error', $e->getMessage());
        }

        $ftpAccount->is_active = $activating;
        $ftpAccount->save();

        $label = $activating ? 'activated' : 'suspended';

        return redirect()->route('portal.dashboard')->with('success', "FTP account {$ftpAccount->username} {$label}.");
    }
}
