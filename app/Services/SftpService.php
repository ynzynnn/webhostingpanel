<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            // 1. Ensure Linux user exists
            @shell_exec("id -u " . escapeshellarg($sysUser) . " 2>&1 || sudo /usr/sbin/useradd -m -s /bin/bash -g www-data " . escapeshellarg($sysUser) . " 2>&1");

            // 2. Set password via chpasswd
            $cmd = "echo " . escapeshellarg("{$sysUser}:{$newPassword}") . " | sudo /usr/sbin/chpasswd 2>&1";
            $output = @shell_exec($cmd);

            // 3. Ensure permissions on home directory for SFTP access
            $vhostDir = dirname($website->document_root);
            @shell_exec("sudo /bin/chown -R {$sysUser}:www-data " . escapeshellarg($vhostDir) . " 2>&1");
            @shell_exec("sudo /bin/chmod 755 " . escapeshellarg($vhostDir) . " 2>&1");
        }

        AuditLogger::log('sftp_password_reset', "Password SFTP untuk Linux user {$sysUser} ({$website->domain_name}) berhasil diperbarui.", $website->user_id);

        return [
            'success' => true,
            'message' => "Password SFTP untuk user {$sysUser} berhasil diperbarui!",
            'username' => $sysUser,
            'password' => $newPassword,
        ];
    }
}
