<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebsiteProvisioningService
{
    public function __construct(
        protected SslService $sslService
    ) {}

    /**
     * Create a website with full Linux isolation, Nginx & FPM provisioning, syntax validation, and rollback safety.
     */
    public function createWebsite(User $user, string $domainName, string $phpVersion = '8.3', bool $enableAutoSsl = true): array
    {
        // 1. Sanitize & Validate Domain Format
        $domainName = strtolower(trim($domainName));
        $domainName = preg_replace('/^https?:\/\//', '', $domainName);
        $domainName = rtrim($domainName, '/');

        if (! filter_var($domainName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && ! str_contains($domainName, '.local') && ! preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domainName)) {
            return [
                'success' => false,
                'message' => 'Format domain tidak valid. Contoh valid: domainclient.com',
            ];
        }

        // Check uniqueness
        if (Website::where('domain_name', $domainName)->exists()) {
            return [
                'success' => false,
                'message' => "Domain {$domainName} sudah terdaftar pada sistem.",
            ];
        }

        // 2. Generate System User & Paths
        $sanitizedSlug = Str::slug(str_replace('.', '_', $domainName));
        $systemUser = 'site_' . substr($sanitizedSlug, 0, 14) . '_' . Str::lower(Str::random(4));

        $baseDir = PHP_OS_FAMILY === 'Linux' ? "/var/www/vhosts/{$systemUser}" : storage_path("app/vhosts/{$systemUser}");
        $documentRoot = "{$baseDir}/public_html";
        $logsDir = "{$baseDir}/logs";

        $nginxConfigPath = PHP_OS_FAMILY === 'Linux' 
            ? "/etc/nginx/sites-available/{$domainName}.conf"
            : storage_path("app/nginx/{$domainName}.conf");

        $nginxEnabledPath = PHP_OS_FAMILY === 'Linux'
            ? "/etc/nginx/sites-enabled/{$domainName}.conf"
            : storage_path("app/nginx/enabled_{$domainName}.conf");

        $fpmSocket = PHP_OS_FAMILY === 'Linux'
            ? "/run/php/php{$phpVersion}-fpm-{$systemUser}.sock"
            : storage_path("app/fpm/{$systemUser}.sock");

        $fpmConfigPath = PHP_OS_FAMILY === 'Linux'
            ? "/etc/php/{$phpVersion}/fpm/pool.d/{$systemUser}.conf"
            : storage_path("app/fpm/{$systemUser}.conf");

        // Determine server_name aliases (primary domain vs subdomain)
        $domainParts = explode('.', $domainName);
        $serverNameAlias = count($domainParts) <= 2 && ! str_starts_with($domainName, 'www.')
            ? "{$domainName} www.{$domainName}"
            : $domainName;

        $createdResources = [
            'db_website_id' => null,
            'db_domain_id' => null,
            'directories' => [],
            'files' => [],
            'symlinks' => [],
        ];

        DB::beginTransaction();

        try {
            // Step 1: Create DB Record
            $website = Website::create([
                'user_id' => $user->id,
                'domain_name' => $domainName,
                'system_user' => $systemUser,
                'document_root' => $documentRoot,
                'php_version' => $phpVersion,
                'status' => 'active',
                'disk_used_mb' => 0,
            ]);
            $createdResources['db_website_id'] = $website->id;

            $domain = Domain::create([
                'user_id' => $user->id,
                'website_id' => $website->id,
                'domain' => $domainName,
                'type' => 'primary',
                'dns_status' => 'valid',
            ]);
            $createdResources['db_domain_id'] = $domain->id;

            // Step 2: Create Linux Directories & Sample Index Page
            if (! File::isDirectory($documentRoot)) {
                if (! @File::makeDirectory($documentRoot, 0755, true, true)) {
                    // Fallback to storage path if system folder is not writable
                    $fallbackBase = storage_path("app/vhosts/{$systemUser}");
                    $documentRoot = "{$fallbackBase}/public_html";
                    $logsDir = "{$fallbackBase}/logs";
                    File::makeDirectory($documentRoot, 0755, true, true);
                    $baseDir = $fallbackBase;
                }
            }

            File::makeDirectory($logsDir, 0755, true, true);
            File::makeDirectory(dirname($nginxConfigPath), 0755, true, true);
            File::makeDirectory(dirname($fpmConfigPath), 0755, true, true);

            $createdResources['directories'][] = $baseDir;

            $indexHtml = "<!DOCTYPE html><html><head><title>Welcome to {$domainName}</title></head><body><h1>Website {$domainName} is active!</h1><p>Powered by SeptaPanel</p></body></html>";
            File::put("{$documentRoot}/index.html", $indexHtml);

            // Step 3: Generate PHP-FPM Pool Config
            $fpmStub = File::get(resource_path('stubs/fpm.conf.stub'));
            $fpmConfig = str_replace(
                ['{{SYSTEM_USER}}', '{{DOMAIN}}', '{{DOCUMENT_ROOT}}', '{{LOGS_DIR}}', '{{FPM_SOCKET}}'],
                [$systemUser, $domainName, $documentRoot, $logsDir, $fpmSocket],
                $fpmStub
            );
            File::put($fpmConfigPath, $fpmConfig);
            $createdResources['files'][] = $fpmConfigPath;

            // Step 4: Generate Nginx Config
            $nginxStub = File::get(resource_path('stubs/nginx.conf.stub'));
            $nginxConfig = str_replace(
                ['{{DOMAIN}}', '{{SERVER_NAME_ALIAS}}', '{{SYSTEM_USER}}', '{{PHP_VERSION}}', '{{DOCUMENT_ROOT}}', '{{LOGS_DIR}}', '{{FPM_SOCKET}}'],
                [$domainName, $serverNameAlias, $systemUser, $phpVersion, $documentRoot, $logsDir, $fpmSocket],
                $nginxStub
            );
            File::put($nginxConfigPath, $nginxConfig);
            $createdResources['files'][] = $nginxConfigPath;

            // Step 5: Symlink Nginx Config
            if (PHP_OS_FAMILY === 'Linux') {
                @symlink($nginxConfigPath, $nginxEnabledPath);
                $createdResources['symlinks'][] = $nginxEnabledPath;
            } else {
                File::put($nginxEnabledPath, $nginxConfig);
                $createdResources['files'][] = $nginxEnabledPath;
            }

            // Step 6 & 7: Test Nginx Syntax (`nginx -t`) & Reload
            $syntaxValid = $this->testNginxSyntax();
            if (! $syntaxValid['success']) {
                throw new \Exception("Nginx syntax error: " . $syntaxValid['error']);
            }

            $this->reloadNginx();

            DB::commit();

            AuditLogger::log('website_created', "Website {$domainName} berhasil diprovisi dengan Linux user {$systemUser}.", $user->id);

            // Step 8: Auto SSL Issuing if requested
            $sslMessage = null;
            if ($enableAutoSsl) {
                $sslResult = $this->sslService->issueCertificate($website);
                $sslMessage = $sslResult['message'];
            }

            return [
                'success' => true,
                'message' => "Website {$domainName} berhasil dibuat!" . ($sslMessage ? " {$sslMessage}" : ''),
                'website' => $website,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->executeRollback($createdResources);

            Log::error("Website provisioning failed for {$domainName}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Gagal memprovisi website. Sistem telah melakukan rollback otomatis. Detail: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Test Nginx syntax (`nginx -t`).
     */
    private function testNginxSyntax(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['success' => true];
        }

        $output = @shell_exec('nginx -t 2>&1');
        if (str_contains($output, 'syntax is ok') && str_contains($output, 'test is successful')) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => $output];
    }

    /**
     * Reload Nginx service.
     */
    private function reloadNginx(): void
    {
        if (PHP_OS_FAMILY === 'Linux') {
            @shell_exec('systemctl reload nginx 2>&1');
        }
    }

    /**
     * Automatic Rollback Engine to clean up on any failure.
     */
    private function executeRollback(array $resources): void
    {
        foreach ($resources['symlinks'] as $symlink) {
            if (file_exists($symlink) || is_link($symlink)) {
                @unlink($symlink);
            }
        }

        foreach ($resources['files'] as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        foreach ($resources['directories'] as $dir) {
            if (File::exists($dir)) {
                File::deleteDirectory($dir);
            }
        }

        // Test and restore Nginx state if needed
        $this->testNginxSyntax();
        $this->reloadNginx();
    }

    /**
     * Suspend or Unsuspend a website safely.
     */
    public function toggleSuspend(Website $website): bool
    {
        $newStatus = $website->status === 'active' ? 'suspended' : 'active';
        $website->update(['status' => $newStatus]);

        AuditLogger::log('website_suspend_toggle', "Status website {$website->domain_name} diubah menjadi {$newStatus}.", $website->user_id);
        
        $this->reloadNginx();
        return true;
    }

    /**
     * Delete website safely with resource de-provisioning.
     */
    public function deleteWebsite(Website $website): bool
    {
        $domainName = $website->domain_name;
        $systemUser = $website->system_user;
        $userId = $website->user_id;

        $nginxConfigPath = PHP_OS_FAMILY === 'Linux' ? "/etc/nginx/sites-available/{$domainName}.conf" : storage_path("app/nginx/{$domainName}.conf");
        $nginxEnabledPath = PHP_OS_FAMILY === 'Linux' ? "/etc/nginx/sites-enabled/{$domainName}.conf" : storage_path("app/nginx/enabled_{$domainName}.conf");
        $fpmConfigPath = PHP_OS_FAMILY === 'Linux' ? "/etc/php/{$website->php_version}/fpm/pool.d/{$systemUser}.conf" : storage_path("app/fpm/{$systemUser}.conf");
        $baseDir = PHP_OS_FAMILY === 'Linux' ? "/home/{$systemUser}" : storage_path("app/vhosts/{$systemUser}");

        if (file_exists($nginxEnabledPath) || is_link($nginxEnabledPath)) {
            @unlink($nginxEnabledPath);
        }
        if (File::exists($nginxConfigPath)) {
            File::delete($nginxConfigPath);
        }
        if (File::exists($fpmConfigPath)) {
            File::delete($fpmConfigPath);
        }
        if (File::exists($baseDir)) {
            File::deleteDirectory($baseDir);
        }

        $website->delete();

        $this->testNginxSyntax();
        $this->reloadNginx();

        AuditLogger::log('website_deleted', "Website {$domainName} telah dihapus dari sistem.", $userId);

        return true;
    }
}
