<?php

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class MysqlService
{
    private static function connection(): PDO
    {
        $host = env('MYSQL_ADMIN_HOST', '127.0.0.1');
        $port = env('MYSQL_ADMIN_PORT', '3306');
        $user = env('MYSQL_ADMIN_USER', 'root');
        $pass = env('MYSQL_ADMIN_PASSWORD', '');

        try {
            return new PDO(
                "mysql:host={$host};port={$port}",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Could not connect to MySQL: ' . $e->getMessage());
        }
    }

    public static function createDatabase(string $name): void
    {
        try {
            static::connection()->exec("CREATE DATABASE IF NOT EXISTS `{$name}`");
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create database: ' . $e->getMessage());
        }
    }

    public static function dropDatabase(string $name): void
    {
        try {
            static::connection()->exec("DROP DATABASE IF EXISTS `{$name}`");
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to drop database: ' . $e->getMessage());
        }
    }

    public static function createUser(string $username, string $password, string $database): void
    {
        try {
            $pdo = static::connection();
            $pdo->exec("CREATE USER `{$username}`@'localhost' IDENTIFIED BY " . $pdo->quote($password));
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$database}`.* TO `{$username}`@'localhost'");
            $pdo->exec("FLUSH PRIVILEGES");
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create MySQL user: ' . $e->getMessage());
        }
    }

    public static function updateUserPassword(string $username, string $password): void
    {
        try {
            $pdo = static::connection();
            $pdo->exec("ALTER USER `{$username}`@'localhost' IDENTIFIED BY " . $pdo->quote($password));
            $pdo->exec("FLUSH PRIVILEGES");
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to update MySQL user password: ' . $e->getMessage());
        }
    }

    public static function dropUser(string $username): void
    {
        try {
            $pdo = static::connection();
            $pdo->exec("DROP USER IF EXISTS `{$username}`@'localhost'");
            $pdo->exec("FLUSH PRIVILEGES");
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to drop MySQL user: ' . $e->getMessage());
        }
    }
}
