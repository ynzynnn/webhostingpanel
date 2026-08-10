<?php

namespace App\Services;

use App\Models\SslCertificate;
use App\Models\Website;
use Illuminate\Support\Facades\Log;

class SslService
{
    /**
     * Issue Let's Encrypt SSL certificate for a website domain.
     */
    public function issueCertificate(Website $website, bool $autoEnableSsl = true): array
    {
        $domain = $website->domain_name;

        // 1. Validate DNS Resolution before issuing
        $dnsStatus = $this->checkDnsResolution($domain);
        if (! $dnsStatus['valid']) {
            return [
                'success' => false,
                'message' => "DNS domain {$domain} belum mengarah ke IP server. Silakan arahkan A Record DNS terlebih dahulu.",
                'dns_details' => $dnsStatus,
            ];
        }

        // 2. Execute Certbot / Privilege Service issuing command
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/usr/bin/certbot')) {
            $cmd = "sudo /usr/bin/certbot certonly --webroot -w " . escapeshellarg($website->document_root) . " -d " . escapeshellarg($domain) . " --non-interactive --agree-tos --register-unsafely-without-email 2>&1";
            $output = @shell_exec($cmd);

            if (! str_contains($output, 'Congratulations') && ! str_contains($output, 'Certificate not yet due for renewal')) {
                Log::error("Certbot issuing failed for {$domain}: {$output}");
                
                $userFriendlyMsg = "Gagal menerbitkan SSL untuk {$domain}. Pastikan A-Record DNS domain sudah ter-pointing ke IP server ini dan tidak terhalang Proxy Cloudflare/Firewall.";
                if (str_contains($output, 'failed to authenticate') || str_contains($output, 'authenticator: webroot')) {
                    $userFriendlyMsg = "Let's Encrypt gagal melakukan verifikasi DNS domain {$domain}. Hal ini terjadi jika A-Record DNS belum mengarah ke IP VPS ini atau masih dalam proses propagasi DNS (1-15 menit).";
                }

                return [
                    'success' => false,
                    'message' => $userFriendlyMsg,
                ];
            }

            // Ensure Nginx read permissions on newly issued cert files
            @shell_exec("sudo /bin/chgrp -R www-data /etc/letsencrypt/live /etc/letsencrypt/archive 2>&1");
            @shell_exec("sudo /bin/chmod -R 755 /etc/letsencrypt/live /etc/letsencrypt/archive 2>&1");
            @shell_exec("sudo /bin/chmod 644 /etc/letsencrypt/archive/*/*.pem 2>&1");

            // 3. Auto Reconfigure Nginx VirtualHost with SSL (Port 443 & HTTP->HTTPS Redirect)
            $this->reconfigureNginxSsl($website);
        }

        // 4. Record or Update SSL Certificate DB
        $ssl = SslCertificate::updateOrCreate(
            ['website_id' => $website->id, 'domain' => $domain],
            [
                'user_id' => $website->user_id,
                'status' => 'active',
                'issuer' => "Let's Encrypt Authority",
                'expires_at' => now()->addDays(90),
            ]
        );

        AuditLogger::log('ssl_issued', "Sertifikat SSL Let's Encrypt berhasil diterbitkan dan Nginx di-rekonfigurasi untuk {$domain}.", $website->user_id);

        return [
            'success' => true,
            'message' => "Sertifikat SSL Let's Encrypt berhasil diterbitkan dan Nginx HTTPS berhasil diaktifkan untuk {$domain}!",
            'ssl' => $ssl,
        ];
    }

    /**
     * Reconfigure Nginx VirtualHost to enable SSL (Port 443) & HTTP->HTTPS redirect.
     */
    public function reconfigureNginxSsl(Website $website): void
    {
        $domain = $website->domain_name;
        $systemUser = $website->system_user;
        $phpVersion = $website->php_version;
        $documentRoot = $website->document_root;
        $logsDir = dirname($documentRoot) . "/logs";

        $fpmSocket = PHP_OS_FAMILY === 'Linux'
            ? "/run/php/php{$phpVersion}-fpm-{$systemUser}.sock"
            : storage_path("app/fpm/{$systemUser}.sock");

        $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        $keyPath = "/etc/letsencrypt/live/{$domain}/privkey.pem";

        if (! file_exists($certPath) && PHP_OS_FAMILY !== 'Linux') {
            $certPath = storage_path("app/ssl/{$domain}.crt");
            $keyPath = storage_path("app/ssl/{$domain}.key");
        }

        $domainParts = explode('.', $domain);
        $serverNameAlias = count($domainParts) <= 2 && ! str_starts_with($domain, 'www.')
            ? "{$domain} www.{$domain}"
            : $domain;

        $sslStub = \File::get(resource_path('stubs/nginx-ssl.conf.stub'));
        $sslNginxConfig = str_replace(
            ['{{DOMAIN}}', '{{SERVER_NAME_ALIAS}}', '{{SYSTEM_USER}}', '{{PHP_VERSION}}', '{{DOCUMENT_ROOT}}', '{{LOGS_DIR}}', '{{FPM_SOCKET}}', '{{SSL_CERT_PATH}}', '{{SSL_KEY_PATH}}'],
            [$domain, $serverNameAlias, $systemUser, $phpVersion, $documentRoot, $logsDir, $fpmSocket, $certPath, $keyPath],
            $sslStub
        );

        $stagedNginxPath = storage_path("app/nginx/{$domain}.conf");
        \File::makeDirectory(dirname($stagedNginxPath), 0755, true, true);
        \File::put($stagedNginxPath, $sslNginxConfig);

        if (PHP_OS_FAMILY === 'Linux') {
            $etcNginxAvail = "/etc/nginx/sites-available/{$domain}.conf";
            $etcNginxEnabled = "/etc/nginx/sites-enabled/{$domain}.conf";

            if (is_writable("/etc/nginx/sites-available")) {
                \File::put($etcNginxAvail, $sslNginxConfig);
                @unlink($etcNginxEnabled);
                @symlink($etcNginxAvail, $etcNginxEnabled);
            } else {
                @shell_exec("sudo /bin/cp " . escapeshellarg($stagedNginxPath) . " " . escapeshellarg($etcNginxAvail) . " 2>&1");
                @shell_exec("sudo /bin/ln -sf " . escapeshellarg($etcNginxAvail) . " " . escapeshellarg($etcNginxEnabled) . " 2>&1");
            }

            // Test syntax & reload Nginx
            $testCmd = shell_exec("sudo /usr/sbin/nginx -t 2>&1");
            if (str_contains($testCmd, 'syntax is ok')) {
                shell_exec("sudo /usr/bin/systemctl reload nginx 2>&1");
            } else {
                Log::error("Nginx SSL reconfiguration syntax test failed for {$domain}: {$testCmd}");
            }
        }
    }

    /**
     * Check if domain DNS resolves locally or publicly.
     */
    public function checkDnsResolution(string $domain): array
    {
        if ($domain === 'localhost' || str_ends_with($domain, '.local') || str_ends_with($domain, '.test')) {
            return ['valid' => true, 'ip' => '127.0.0.1', 'note' => 'Local development domain'];
        }

        $records = @dns_get_record($domain, DNS_A);
        if ($records && count($records) > 0) {
            $ip = $records[0]['ip'] ?? null;
            return ['valid' => true, 'ip' => $ip];
        }

        return ['valid' => false, 'ip' => null, 'note' => 'No A Record found'];
    }
}
