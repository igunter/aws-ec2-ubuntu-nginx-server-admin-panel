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

        if ($request->boolean('ssl')) {
            try {
                $this->provisionSsl($account);
                $account->update(['ssl' => true]);
            } catch (\RuntimeException $e) {
                return redirect()->route('accounts.show', $account)
                    ->with('warning', "Account created, but SSL could not be enabled: {$e->getMessage()}");
            }
        }

        return redirect()->route('accounts.index')->with('success', "Account for {$account->domain} created successfully.");
    }

    public function show(Account $account)
    {
        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'slug'   => ['required', 'string', 'max:32', 'regex:/^[a-z][a-z0-9-]*$/', 'unique:accounts,slug,' . $account->id],
            'domain' => ['required', 'string', 'max:255', 'unique:accounts,domain,' . $account->id],
            'email'  => ['nullable', 'string', 'email', 'max:255'],
        ]);

        $slugChanged   = $request->slug   !== $account->slug;
        $domainChanged = $request->domain !== $account->domain;

        if ($slugChanged || $domainChanged) {
            try {
                $this->updateServerAccount($account, $request->slug, $request->domain, $slugChanged, $domainChanged);
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        $account->update([
            'slug'   => $request->slug,
            'domain' => $request->domain,
            'email'  => $request->email,
            'ssl'    => $domainChanged ? false : $account->ssl,
        ]);

        return redirect()->route('accounts.show', $account)->with('success', 'Account updated.');
    }

    private function updateServerAccount(Account $account, string $newSlug, string $newDomain, bool $slugChanged, bool $domainChanged): void
    {
        $oldSlug   = $account->slug;
        $oldDomain = $account->domain;
        $oldWebRoot        = "/var/www/{$oldSlug}";
        $newWebRoot        = "/var/www/{$newSlug}";
        $oldSitesAvailable = "/etc/nginx/sites-available/{$oldDomain}";
        $newSitesAvailable = "/etc/nginx/sites-available/{$newDomain}";
        $oldSitesEnabled   = "/etc/nginx/sites-enabled/{$oldDomain}";
        $newSitesEnabled   = "/etc/nginx/sites-enabled/{$newDomain}";

        if ($slugChanged) {
            $this->cmd(['sudo', 'mv', $oldWebRoot, $newWebRoot], 'Failed to rename web root.');
            $this->cmd(['sudo', 'usermod', '-l', $newSlug, $oldSlug], 'Failed to rename system user.');
            $this->cmd(['sudo', 'groupmod', '-n', $newSlug, $oldSlug], 'Failed to rename system group.');
            $this->cmd(['sudo', 'usermod', '-d', $newWebRoot, $newSlug], 'Failed to update home directory.');
            $this->cmd(['sudo', 'chown', '-R', "{$newSlug}:{$newSlug}", $newWebRoot], 'Failed to update web root ownership.');
        }

        $this->cmd(['sudo', 'rm', '-f', $oldSitesEnabled], 'Failed to remove old symlink.');

        if ($domainChanged) {
            $this->cmd(['sudo', 'rm', '-f', $oldSitesAvailable], 'Failed to remove old Nginx config.');

            if ($account->ssl) {
                $this->cmd(['sudo', 'certbot', 'delete', '--cert-name', $oldDomain, '--non-interactive'], 'Failed to remove old SSL certificate.');
            }
        }

        $result = Process::input($this->nginxConfig($newDomain, $newWebRoot))
            ->run(['sudo', 'tee', $newSitesAvailable]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to write Nginx config. ' . trim($result->errorOutput()));
        }

        $this->cmd(['sudo', 'ln', '-sf', $newSitesAvailable, $newSitesEnabled], 'Failed to enable site.');
        $this->cmd(['sudo', 'nginx', '-t'], 'Nginx config test failed.');
        $this->cmd(['sudo', 'systemctl', 'reload', 'nginx'], 'Failed to reload Nginx.');
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

    public function toggleSsl(Account $account)
    {
        $enabling = ! $account->ssl;

        try {
            if ($enabling) {
                $this->provisionSsl($account);
            } else {
                $this->deprovisionSsl($account);
            }
        } catch (\RuntimeException $e) {
            return redirect()->route('accounts.show', $account)->with('error', $e->getMessage());
        }

        $account->update(['ssl' => $enabling]);

        return redirect()->route('accounts.show', $account)
            ->with('success', "SSL " . ($enabling ? 'enabled' : 'disabled') . " for {$account->domain}.");
    }

    private function checkDnsPointsHere(string $domain): void
    {
        $serverIp = env('SERVER_IP') ?: gethostbyname(gethostname());

        $records = @dns_get_record($domain, DNS_A);

        if (empty($records)) {
            throw new \RuntimeException("No DNS A record found for {$domain}. Point the domain to this server ({$serverIp}) before enabling SSL.");
        }

        $domainIps = array_column($records, 'ip');

        if (! in_array($serverIp, $domainIps, true)) {
            throw new \RuntimeException("DNS check failed: {$domain} resolves to " . implode(', ', $domainIps) . " but this server is {$serverIp}. Update your DNS before enabling SSL.");
        }
    }

    private function provisionSsl(Account $account): void
    {
        $this->checkDnsPointsHere($account->domain);

        $command = [
            'sudo', 'certbot', '--nginx',
            '-d', $account->domain,
            '--agree-tos',
            '--non-interactive',
        ];

        if ($account->email) {
            array_push($command, '--email', $account->email);
        } else {
            $command[] = '--register-unsafely-without-email';
        }

        $this->cmd($command, 'Failed to provision SSL certificate.');
    }

    private function deprovisionSsl(Account $account): void
    {
        $this->cmd(
            ['sudo', 'certbot', 'delete', '--cert-name', $account->domain, '--non-interactive'],
            'Failed to remove SSL certificate.'
        );
    }

    public function suspend(Account $account)
    {
        $sitesAvailable = "/etc/nginx/sites-available/{$account->domain}";
        $activating = ! $account->is_active;

        try {
            if ($activating) {
                $config = $this->nginxConfig($account->domain, "/var/www/{$account->slug}");
                $result = Process::input($config)->run(['sudo', 'tee', $sitesAvailable]);
                if (! $result->successful()) {
                    throw new \RuntimeException('Failed to restore Nginx config. ' . trim($result->errorOutput()));
                }
                $this->cmd(['sudo', 'nginx', '-t'], 'Nginx config test failed.');
                $this->cmd(['sudo', 'systemctl', 'reload', 'nginx'], 'Failed to reload Nginx.');

                if ($account->ssl) {
                    $this->provisionSsl($account);
                }
            } else {
                $config = $this->suspendedNginxConfig($account->domain, $account->ssl);
                $result = Process::input($config)->run(['sudo', 'tee', $sitesAvailable]);
                if (! $result->successful()) {
                    throw new \RuntimeException('Failed to write suspended Nginx config. ' . trim($result->errorOutput()));
                }
                $this->cmd(['sudo', 'nginx', '-t'], 'Nginx config test failed.');
                $this->cmd(['sudo', 'systemctl', 'reload', 'nginx'], 'Failed to reload Nginx.');
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $account->update(['is_active' => $activating]);

        $label = $activating ? 'activated' : 'suspended';
        return back()->with('success', "{$account->domain} {$label}.");
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
        $result = Process::input($welcome)->run(['sudo', 'tee', "{$webRoot}/index.php"]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to create index.php. ' . trim($result->errorOutput()));
        }

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

    private function suspendedNginxConfig(string $domain, bool $ssl = false): string
    {
        $errorPage = "error_page 503 /503.html;\n            location = /503.html {\n                add_header Content-Type text/html;\n                return 503 '<!DOCTYPE html><html><head><title>Service Unavailable</title><style>body{font-family:sans-serif;text-align:center;padding:4rem}h1{font-size:3rem}p{color:#666}</style></head><body><h1>503</h1><p>This site is temporarily unavailable.</p></body></html>';\n            }";

        $sslBlock = $ssl ? <<<NGINX

        server {
            listen 443 ssl;
            server_name {$domain};
            ssl_certificate /etc/letsencrypt/live/{$domain}/fullchain.pem;
            ssl_certificate_key /etc/letsencrypt/live/{$domain}/privkey.pem;
            include /etc/letsencrypt/options-ssl-nginx.conf;
            ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

            location / { return 503; }
            {$errorPage}
        }
        NGINX : '';

        return <<<NGINX
        server {
            listen 80;
            server_name {$domain};

            location / { return 503; }
            {$errorPage}
        }
        {$sslBlock}
        NGINX;
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
