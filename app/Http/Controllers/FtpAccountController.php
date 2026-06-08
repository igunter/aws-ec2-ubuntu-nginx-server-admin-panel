<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FtpAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
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

        try {
            $this->provisionFtpAccount($username, $validated['password'], $ftpRoot);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        FtpAccount::create([
            'account_id'     => $validated['account_id'],
            'username'       => $username,
            'password'       => $validated['password'],
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
        //
    }

    public function update(Request $request, FtpAccount $ftpAccount)
    {
        //
    }

    public function destroy(FtpAccount $ftpAccount)
    {
        //
    }

    public function suspend(FtpAccount $ftpAccount)
    {
        $activating = ! $ftpAccount->is_active;
        $account    = $ftpAccount->account;
        $ftpRoot    = '/var/www/' . $account->slug . $ftpAccount->root_directory;

        try {
            if ($activating) {
                $this->provisionFtpAccount($ftpAccount->username, $ftpAccount->password, $ftpRoot);
            } else {
                $this->deprovisionFtpAccount($ftpAccount->username);
            }
        } catch (\RuntimeException $e) {
            return redirect()->route('ftp-accounts.index')->with('error', $e->getMessage());
        }

        $ftpAccount->is_active = $activating;
        $ftpAccount->save();

        $label = $activating ? 'activated' : 'suspended';
        return redirect()->route('ftp-accounts.index')->with('success', "FTP account {$ftpAccount->username} {$label}.");
    }

    private function provisionFtpAccount(string $username, string $password, string $ftpRoot): void
    {
        $passwdFile  = '/etc/vsftpd/virtual_users.passwd';
        $userConfDir = '/etc/vsftpd/user_conf';

        $this->cmd(['sudo', 'mkdir', '-p', dirname($passwdFile)], 'Failed to create vsftpd directory.');
        $this->cmd(['sudo', 'mkdir', '-p', $userConfDir], 'Failed to create vsftpd user_conf directory.');

        // Ensure the passwd file exists and is secured
        if (! file_exists($passwdFile)) {
            $this->cmd(['sudo', 'touch', $passwdFile], 'Failed to create vsftpd passwd file.');
            $this->cmd(['sudo', 'chmod', '600', $passwdFile], 'Failed to set vsftpd passwd file permissions.');
        }

        $result = Process::run(['sudo', 'htpasswd', '-b', $passwdFile, $username, $password]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to add FTP user. ' . trim($result->errorOutput()));
        }

        $userConf = "local_root={$ftpRoot}\n";
        $result   = Process::input($userConf)->run(['sudo', 'tee', "{$userConfDir}/{$username}"]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to write FTP user config. ' . trim($result->errorOutput()));
        }

        $this->cmd(['sudo', 'systemctl', 'reload', 'vsftpd'], 'Failed to reload vsftpd.');
    }

    private function deprovisionFtpAccount(string $username): void
    {
        $passwdFile = '/etc/vsftpd/virtual_users.passwd';

        $this->cmd(['sudo', 'htpasswd', '-D', $passwdFile, $username], 'Failed to remove FTP user from passwd file.');
        $this->cmd(['sudo', 'systemctl', 'reload', 'vsftpd'], 'Failed to reload vsftpd.');
    }

    private function cmd(array $command, string $errorMessage): void
    {
        $result = Process::run($command);
        if (! $result->successful()) {
            throw new \RuntimeException($errorMessage . ' ' . trim($result->errorOutput()));
        }
    }
}
