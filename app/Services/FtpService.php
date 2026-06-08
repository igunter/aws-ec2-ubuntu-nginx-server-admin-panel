<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class FtpService
{
    private const PASSWD_FILE  = '/etc/vsftpd/virtual_users.passwd';
    private const USER_CONF_DIR = '/etc/vsftpd/user_conf';

    public static function provision(string $username, string $password, string $ftpRoot): void
    {
        static::cmd(['sudo', 'mkdir', '-p', dirname(self::PASSWD_FILE)], 'Failed to create vsftpd directory.');
        static::cmd(['sudo', 'mkdir', '-p', self::USER_CONF_DIR], 'Failed to create vsftpd user_conf directory.');

        $current = @file_get_contents(self::PASSWD_FILE) ?: '';
        $hash    = password_hash($password, PASSWORD_BCRYPT);
        $lines   = array_filter(explode("\n", $current), fn($l) => trim($l) !== '' && ! str_starts_with($l, "{$username}:"));
        $lines[] = "{$username}:{$hash}";

        $result = Process::input(implode("\n", $lines) . "\n")->run(['sudo', 'tee', self::PASSWD_FILE]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to write vsftpd passwd file. ' . trim($result->errorOutput()));
        }

        static::cmd(['sudo', 'chown', 'root:www-data', self::PASSWD_FILE], 'Failed to set vsftpd passwd file ownership.');
        static::cmd(['sudo', 'chmod', '640', self::PASSWD_FILE], 'Failed to set vsftpd passwd file permissions.');

        $result = Process::input("local_root={$ftpRoot}\n")->run(['sudo', 'tee', self::USER_CONF_DIR . "/{$username}"]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to write FTP user config. ' . trim($result->errorOutput()));
        }

        static::cmd(['sudo', 'systemctl', 'reload-or-restart', 'vsftpd'], 'Failed to reload vsftpd.');
    }

    public static function deprovision(string $username): void
    {
        $current = @file_get_contents(self::PASSWD_FILE) ?: '';
        $lines   = array_filter(explode("\n", $current), fn($l) => trim($l) !== '' && ! str_starts_with($l, "{$username}:"));

        $result = Process::input(implode("\n", $lines) . "\n")->run(['sudo', 'tee', self::PASSWD_FILE]);
        if (! $result->successful()) {
            throw new \RuntimeException('Failed to update vsftpd passwd file. ' . trim($result->errorOutput()));
        }

        static::cmd(['sudo', 'rm', '-f', self::USER_CONF_DIR . "/{$username}"], 'Failed to remove FTP user config.');
        static::cmd(['sudo', 'systemctl', 'reload-or-restart', 'vsftpd'], 'Failed to reload vsftpd.');
    }

    private static function cmd(array $command, string $errorMessage): void
    {
        $result = Process::run($command);
        if (! $result->successful()) {
            throw new \RuntimeException($errorMessage . ' ' . trim($result->errorOutput()));
        }
    }
}
