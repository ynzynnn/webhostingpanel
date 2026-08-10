<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DatabaseModel;
use App\Models\Domain;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@septapanel.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'disk_quota_mb' => 50000,
                'disk_used_mb' => 1200,
            ]
        );

        // 2. Create Default Client User
        $client = User::firstOrCreate(
            ['email' => 'client@septapanel.local'],
            [
                'name' => 'Client Demo',
                'password' => Hash::make('password123'),
                'role' => 'client',
                'status' => 'active',
                'disk_quota_mb' => 5000,
                'disk_used_mb' => 420,
            ]
        );

        // 3. Create Sample Data for Client
        $website = Website::firstOrCreate(
            ['domain_name' => 'mycompany.com'],
            [
                'user_id' => $client->id,
                'system_user' => 'site_mycompany',
                'document_root' => '/home/site_mycompany/public_html',
                'php_version' => '8.3',
                'status' => 'active',
                'disk_used_mb' => 420,
            ]
        );

        Domain::firstOrCreate(
            ['domain' => 'mycompany.com'],
            [
                'user_id' => $client->id,
                'website_id' => $website->id,
                'type' => 'primary',
                'dns_status' => 'valid',
            ]
        );

        DatabaseModel::firstOrCreate(
            ['db_name' => 'client_mycompany_db'],
            [
                'user_id' => $client->id,
                'db_user' => 'client_mycompany_usr',
            ]
        );

        // 4. Initial Audit Logs
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'system_init',
            'description' => 'SeptaPanel Control Panel initialized successfully.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
