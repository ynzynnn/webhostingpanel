<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\SftpService;
use Illuminate\Console\Command;

class EnableSftpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sftp:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure OpenSSH to allow SFTP Password Authentication for all hosting users';

    /**
     * Execute the console command.
     */
    public function handle(SftpService $sftpService)
    {
        $this->info('Configuring OpenSSH for SFTP Password Authentication...');

        if (PHP_OS_FAMILY !== 'Linux') {
            $this->info('Skipping: Non-Linux environment.');
            return 0;
        }

        // 1. Configure OpenSSH
        $sftpService->ensureSshdConfig();

        // 2. Ensure all website system users exist with bash shell and www-data group
        $websites = Website::all();
        foreach ($websites as $w) {
            $sysUser = $w->system_user;
            $vhostDir = dirname($w->document_root);

            $this->info("Setting up SFTP Linux user: {$sysUser}");

            @shell_exec("id -u " . escapeshellarg($sysUser) . " 2>&1 || sudo /usr/sbin/useradd -m -d " . escapeshellarg($vhostDir) . " -s /bin/bash -g www-data " . escapeshellarg($sysUser) . " 2>&1");
            @shell_exec("sudo /usr/sbin/usermod -s /bin/bash -g www-data " . escapeshellarg($sysUser) . " 2>&1");
            @shell_exec("sudo /bin/chown -R {$sysUser}:www-data " . escapeshellarg($vhostDir) . " 2>&1");
            @shell_exec("sudo /bin/chmod 755 " . escapeshellarg($vhostDir) . " 2>&1");
        }

        // 3. Reload SSH daemon
        shell_exec("sudo /usr/bin/systemctl reload ssh || sudo /usr/bin/systemctl reload sshd 2>&1");

        $this->info('OpenSSH SFTP Password Authentication has been configured successfully!');
        $this->info('You can now log in via FileZilla / WinSCP using your SFTP username and password.');

        return 0;
    }
}
