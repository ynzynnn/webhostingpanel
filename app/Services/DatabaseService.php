<?php

namespace App\Services;

use App\Models\DatabaseModel;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseService
{
    /**
     * Create a MariaDB database and user with prefixing and proper privileges.
     */
    public function createDatabase(User $user, string $dbSuffix, string $password, ?int $websiteId = null): array
    {
        // 1. Sanitize Prefix & Names
        $userPrefix = Str::slug(explode('@', $user->email)[0]);
        $cleanSuffix = Str::slug($dbSuffix, '_');
        
        $dbName = strtolower(substr("{$userPrefix}_{$cleanSuffix}", 0, 32));
        $dbUsername = strtolower(substr("{$userPrefix}_{$cleanSuffix}", 0, 16));

        // Check uniqueness
        if (DatabaseModel::where('name', $dbName)->exists()) {
            return [
                'success' => false,
                'message' => "Database {$dbName} sudah ada pada sistem.",
            ];
        }

        try {
            // 2. Execute MariaDB Queries
            if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                DB::statement("CREATE USER IF NOT EXISTS '{$dbUsername}'@'localhost' IDENTIFIED BY ?;", [$password]);
                DB::statement("ALTER USER '{$dbUsername}'@'localhost' IDENTIFIED BY ?;", [$password]);
                DB::statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUsername}'@'localhost';");
                DB::statement("FLUSH PRIVILEGES;");
            }

            // 3. Save Record to DatabaseModel
            $database = DatabaseModel::create([
                'user_id' => $user->id,
                'website_id' => $websiteId,
                'name' => $dbName,
                'username' => $dbUsername,
                'host' => '127.0.0.1',
                'port' => 3306,
            ]);

            AuditLogger::log('database_created', "Database MariaDB {$dbName} dengan user {$dbUsername} berhasil dibuat.", $user->id);

            return [
                'success' => true,
                'message' => "Database {$dbName} & user {$dbUsername} berhasil dibuat!",
                'database' => $database,
                'db_name' => $dbName,
                'db_username' => $dbUsername,
                'db_password' => $password,
            ];

        } catch (\Throwable $e) {
            Log::error("Failed to create database {$dbName}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Gagal membuat database MariaDB: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete MariaDB database and user.
     */
    public function deleteDatabase(DatabaseModel $database): array
    {
        $dbName = $database->name;
        $dbUsername = $database->username;
        $userId = $database->user_id;

        try {
            if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
                DB::statement("DROP DATABASE IF EXISTS `{$dbName}`;");
                DB::statement("DROP USER IF EXISTS '{$dbUsername}'@'localhost';");
                DB::statement("FLUSH PRIVILEGES;");
            }

            $database->delete();

            AuditLogger::log('database_deleted', "Database MariaDB {$dbName} & user {$dbUsername} berhasil dihapus.", $userId);

            return [
                'success' => true,
                'message' => "Database {$dbName} berhasil dihapus!",
            ];

        } catch (\Throwable $e) {
            Log::error("Failed to delete database {$dbName}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Gagal menghapus database: " . $e->getMessage(),
            ];
        }
    }
}
