<?php
namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug'   => ['required', 'string', 'max:32', 'regex:/^[a-z][a-z0-9-]*$/', 'unique:accounts'],
            'domain' => ['required', 'string', 'max:255', 'unique:accounts'],
            'email'  => ['nullable', 'string', 'email', 'max:255'],
        ]);

        $account = Account::create([
            'slug'   => $request->slug,
            'domain' => $request->domain,
            'email'  => $request->email,
        ]);

        try {
            $this->provisionAccount($account);
        } catch (\RuntimeException $e) {
            $account->delete();
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('accounts.index')->with('success', "Account for {$account->domain} created successfully.");
    }

    public function show(Account $account)
    {
        //
    }

    public function edit(Account $account)
    {
        //
    }

    public function update(Request $request, Account $account)
    {
        //
    }

    public function destroy(Account $account)
    {
        $slug   = $account->slug;
        $domain = $account->domain;
        $sitesAvailable = "/etc/nginx/sites-available/{$domain}";
        $sitesEnabled   = "/etc/nginx/sites-enabled/{$domain}";
        $webRoot        = "/var/www/{$slug}";

        $this->cmd(['sudo', 'rm', '-f', $sitesEnabled],  'Failed to remove site from sites-enabled.');
        $this->cmd(['sudo', 'rm', '-f', $sitesAvailable], 'Failed to remove site from sites-available.');
        $this->cmd(['sudo', 'nginx', '-t'],               'Nginx config test failed.');
        $this->cmd(['sudo', 'systemctl', 'reload', 'nginx'], 'Failed to reload Nginx.');
        $this->cmd(['sudo', 'rm', '-rf', $webRoot],       'Failed to remove web root.');
        $this->cmd(['sudo', 'userdel', '-r', $slug],      'Failed to remove system user.');

        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Account for {$domain} deleted.");
    }

    public function suspend(Account $account)
    {
        //
    }

    private function provisionAccount(Account $account): void
    {
        $slug   = $account->slug;
        $domain = $account->domain;
        $webRoot = "/var/www/{$slug}";
        $sitesAvailable = "/etc/nginx/sites-available/{$domain}";
        $sitesEnabled   = "/etc/nginx/sites-enabled/{$domain}";

        $this->cmd(['sudo', 'useradd', '-m', '-s', '/bin/bash', $slug],
            'Failed to create system user.');

        $this->cmd(['sudo', 'mkdir', '-p', $webRoot],
            'Failed to create web root.');

        $welcome = "<?php\necho '<h1>Welcome, {$domain}</h1>';\n";
        Process::input($welcome)->run(['sudo', 'tee', "{$webRoot}/index.php"]);

        $this->cmd(['sudo', 'chown', '-R', "{$slug}:{$slug}", $webRoot],
            'Failed to set web root ownership.');

        Process::input($this->nginxConfig($domain, $webRoot))
            ->run(['sudo', 'tee', $sitesAvailable]);

        $this->cmd(['sudo', 'ln', '-sf', $sitesAvailable, $sitesEnabled],
            'Failed to enable site.');

        $this->cmd(['sudo', 'nginx', '-t'],
            'Nginx config test failed.');

        $this->cmd(['sudo', 'systemctl', 'reload', 'nginx'],
            'Failed to reload Nginx.');
    }

    private function cmd(array $command, string $errorMessage): void
    {
        $result = Process::run($command);
        if (! $result->successful()) {
            throw new \RuntimeException($errorMessage . ' ' . trim($result->errorOutput()));
        }
    }

    private function nginxConfig(string $domain, string $webRoot): string
    {
        $phpVersion = env('PHP_VERSION', '8.3');

        return <<<NGINX
        server {
            listen 80;
            server_name {$domain};
            root {$webRoot};

            index index.php index.html;

            location / {
                try_files \$uri \$uri/ /index.php?\$query_string;
            }

            location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php{$phpVersion}-fpm.sock;
                fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
                include fastcgi_params;
            }

            location ~ /\.(?!well-known).* {
                deny all;
            }
        }
        NGINX;
    }
}
