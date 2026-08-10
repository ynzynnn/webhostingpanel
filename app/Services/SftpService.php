<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SftpService
{
    /**
     * Get SFTP connection credentials and host details for a website.
     */
    public function getSftpDetails(Website $website): array
    {
        $serverIp = request()->getHttpHost() ?: '127.0.0.1';
        $serverIp = explode(':', $serverIp)[0];

        return [
            'host' => $serverIp,
            'port' => 22,
            'username' => $website->system_user,
            'document_root' => $website->document_root,
            'connection_url' => "sftp://{$website->system_user}@{$serverIp}:22",
        ];
    }

    /**
     * Reset or set password for the website's Linux SFTP system user.
     */
    public function resetSftpPassword(Website $website, string $newPassword): array
    {
        $sysUser = $website->system_user;

        if (PHP_OS_FAMILY === 'Linux') {
            // 1. Ensure OpenSSH allows password authentication for hosting users
            $this->ensureSshdConfig();

            // 2. Ensure Linux user exists with valid shell and home directory
            $vhostDir = dirname($website->document_root);
            @shell_exec("id -u " . escapeshellarg($sysUser) . " 2>&1 || sudo /usr/sbin/useradd -m -d " . escapeshellarg($vhostDir) . " -s /bin/bash -g www-data " . escapeshellarg($sysUser) . " 2>&1");

            // Make sure user shell is valid
            @shell_exec("sudo /usr/sbin/usermod -s /bin/bash -g www-data " . escapeshellarg($sysUser) . " 2>&1");

            // 3. Set password via chpasswd
            $cmd = "echo " . escapeshellarg("{$sysUser}:{$newPassword}") . " | sudo /usr/sbin/chpasswd 2>&1";
            $output = @shell_exec($cmd);

            // 4. Ensure permissions on home directory for SFTP access
            @shell_exec("sudo /bin/chown -R {$sysUser}:www-data " . escapeshellarg($vhostDir) . " 2>&1");
            @shell_exec("sudo /bin/chmod 755 " . escapeshellarg($vhostDir) . " 2>&1");
        }

        AuditLogger::log('sftp_password_reset', "Password SFTP untuk Linux user {$sysUser} ({$website->domain_name}) berhasil diperbarui.", $website->user_id);

        return [
            'success' => true,
            'message' => "Password SFTP untuk user {$sysUser} berhasil diperbarui! Silakan coba koneksi FileZilla/WinSCP kembali.",
            'username' => $sysUser,
            'password' => $newPassword,
        ];
    }

    /**
     * Ensure OpenSSH enables PasswordAuthentication for hosting users.
     */
    public function ensureSshdConfig(): void
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $confPath = "/etc/ssh/sshd_config.d/septapanel.conf";
            $sshdContent = "# SeptaPanel OpenSSH SFTP Configuration\nMatch Group www-data\n    PasswordAuthentication yes\n";

            $tmpConf = "/tmp/sftp_sshd.conf";
            @file_put_contents($tmpConf, $sshdContent);

            if (File::isDirectory('/etc/ssh/sshd_config.d')) {
                @shell_exec("sudo /bin/cp " . escapeshellarg($tmpConf) . " " . escapeshellarg($confPath) . " 2>&1");
            }

            // Also ensure global PasswordAuthentication yes if disabled
            @shell_exec("sudo /bin/sed -i 's/^PasswordAuthentication no/PasswordAuthentication yes/g' /etc/ssh/sshd_config 2>&1");

            // Reload SSH service
            @shell_exec("sudo /usr/bin/systemctl reload ssh || sudo /usr/bin/systemctl reload sshd 2>&1");
            @unlink($tmpConf);
        }
    }
}
