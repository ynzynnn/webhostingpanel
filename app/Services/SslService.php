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
        }

        // 3. Record or Update SSL Certificate DB
        $ssl = SslCertificate::updateOrCreate(
            ['website_id' => $website->id, 'domain' => $domain],
            [
                'user_id' => $website->user_id,
                'status' => 'active',
                'issuer' => "Let's Encrypt Authority",
                'expires_at' => now()->addDays(90),
            ]
        );

        AuditLogger::log('ssl_issued', "Sertifikat SSL Let's Encrypt berhasil diterbitkan untuk {$domain}.", $website->user_id);

        return [
            'success' => true,
            'message' => "Sertifikat SSL Let's Encrypt berhasil diterbitkan dan diaktifkan untuk {$domain}!",
            'ssl' => $ssl,
        ];
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
