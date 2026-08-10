<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixFpmPoolsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpm:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix and validate all PHP-FPM pools in /etc/php/8.3/fpm/pool.d/ to resolve exit-code 78 errors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting PHP-FPM Pool Diagnostics & Fix...');

        if (PHP_OS_FAMILY !== 'Linux') {
            $this->info('Skipping: Non-Linux environment.');
            return 0;
        }

        $fpmPoolDir = '/etc/php/8.3/fpm/pool.d';

        if (! File::isDirectory($fpmPoolDir)) {
            $this->error("Directory {$fpmPoolDir} not found.");
            return 1;
        }

        // 1. Ensure default www.conf is valid or reset
        $websites = Website::all();
        $validSystemUsers = $websites->pluck('system_user')->toArray();

        // 2. Rewrite each website's FPM pool config
        foreach ($websites as $website) {
            $sysUser = $website->system_user;
            $domain = $website->domain_name;
            $documentRoot = $website->document_root;
            // Ensure chdir and logs directory exist
            if (! File::isDirectory($documentRoot)) {
                File::makeDirectory($documentRoot, 0755, true, true);
            }
            if (! File::isDirectory($logsDir)) {
                File::makeDirectory($logsDir, 0755, true, true);
            }

            $confContent = <<<INI
; SeptaPanel PHP-FPM Pool Configuration
; Pool: {$sysUser}
; Domain: {$domain}

[{$sysUser}]
user = www-data
group = www-data

listen = {$fpmSocket}
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 10s
pm.max_requests = 500

chdir = {$documentRoot}

php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[memory_limit] = 256M
php_admin_flag[log_errors] = on
php_admin_value[error_log] = {$logsDir}/php_error.log
INI;

            $poolPath = "{$fpmPoolDir}/{$sysUser}.conf";
            $stagedPath = storage_path("app/fpm/{$sysUser}.conf");

            File::makeDirectory(dirname($stagedPath), 0755, true, true);
            File::put($stagedPath, $confContent);

            @shell_exec("sudo /bin/cp " . escapeshellarg($stagedPath) . " " . escapeshellarg($poolPath) . " 2>&1");
            $this->info("Rewritten pool config: {$poolPath}");
        }

        // 3. Clean up broken/orphaned pool files
        $confFiles = File::files($fpmPoolDir);
        foreach ($confFiles as $file) {
            $filename = $file->getFilename();
            if ($filename === 'www.conf') {
                continue;
            }

            $sysUserFromFilename = str_replace('.conf', '', $filename);
            if (! in_array($sysUserFromFilename, $validSystemUsers)) {
                $this->warn("Removing orphaned pool file: {$filename}");
                @shell_exec("sudo /bin/rm -f " . escapeshellarg($file->getPathname()) . " 2>&1");
            } else {
                $content = File::get($file->getPathname());
                if (preg_match('/chdir\s*=\s*(.*)/', $content, $matches)) {
                    $chdirPath = trim($matches[1]);
                    if (! File::isDirectory($chdirPath)) {
                        $this->warn("Creating missing chdir directory for {$filename}: {$chdirPath}");
                        File::makeDirectory($chdirPath, 0755, true, true);
                    }
                }
            }
        }

        // 4. Test PHP-FPM Syntax
        $testOutput = shell_exec("sudo /usr/sbin/php-fpm8.3 -t 2>&1");
        $this->line($testOutput);

        if (str_contains($testOutput, 'test is successful')) {
            $this->info('PHP-FPM syntax check PASSED! Restarting php8.3-fpm service...');
            shell_exec("sudo /usr/bin/systemctl restart php8.3-fpm 2>&1");
            shell_exec("sudo /usr/bin/systemctl reload nginx 2>&1");
            $this->info('Service php8.3-fpm restarted successfully!');
            return 0;
        } else {
            $this->error('PHP-FPM syntax test failed. Check logs above.');
            return 1;
        }
    }
}
