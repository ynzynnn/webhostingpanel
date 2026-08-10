<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Website;
use App\Services\AuditLogger;
use App\Services\SslService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    public function __construct(
        protected SslService $sslService
    ) {}

    /**
     * Display list of domains (Admin sees all, Client sees own).
     */
    public function index()
    {
        $query = Domain::with(['website', 'user'])->latest();

        if (auth()->user()->isClient()) {
            $query->where('user_id', auth()->id());
            $websites = Website::where('user_id', auth()->id())->latest()->get();
        } else {
            $websites = Website::latest()->get();
        }

        $domains = $query->get();

        return view('domains.index', compact('domains', 'websites'));
    }

    /**
     * Store a new domain alias or subdomain mapped to a website.
     */
    public function store(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'domain' => [
                'required',
                'string',
                'unique:domains,domain',
                'regex:/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]$/i',
            ],
            'type' => 'required|in:alias,subdomain',
        ], [
            'domain.unique' => 'Domain atau subdomain ini sudah terdaftar di sistem.',
            'domain.regex' => 'Format nama domain tidak valid. Contoh: alias.com atau sub.domain.com',
        ]);

        $website = Website::findOrFail($request->website_id);

        // Security check: Client can only map to their own website
        if (auth()->user()->isClient() && $website->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }

        $domainName = strtolower(trim($request->domain));
        $domainName = preg_replace('/^https?:\/\//', '', $domainName);
        $domainName = rtrim($domainName, '/');

        // Check DNS A-Record status
        $dnsStatusResult = $this->sslService->checkDnsResolution($domainName);
        $dnsStatus = $dnsStatusResult['valid'] ? 'valid' : 'invalid';

        $domain = Domain::create([
            'user_id' => $website->user_id,
            'website_id' => $website->id,
            'domain' => $domainName,
            'type' => $request->type,
            'dns_status' => $dnsStatus,
        ]);

        // Sync Nginx VirtualHost server_name
        $this->syncNginxDomainAliases($website);

        AuditLogger::log('domain_added', "Domain alias {$domainName} ({$request->type}) berhasil ditambahkan ke website {$website->domain_name}.", auth()->id());

        return redirect()->back()->with('success', "Domain alias {$domainName} berhasil ditambahkan dan dipetakan ke {$website->domain_name}!");
    }

    /**
     * Check DNS resolution for a domain.
     */
    public function checkDns(Domain $domain)
    {
        if (auth()->user()->isClient() && $domain->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }

        $dnsResult = $this->sslService->checkDnsResolution($domain->domain);
        $domain->update([
            'dns_status' => $dnsResult['valid'] ? 'valid' : 'invalid',
        ]);

        $statusMsg = $dnsResult['valid'] 
            ? "DNS Domain {$domain->domain} valid & ter-pointing ke IP {$dnsResult['ip']}!"
            : "DNS Domain {$domain->domain} belum mengarah ke IP server.";

        return redirect()->back()->with($dnsResult['valid'] ? 'success' : 'error', $statusMsg);
    }

    /**
     * Delete a domain alias.
     */
    public function destroy(Domain $domain)
    {
        if (auth()->user()->isClient() && $domain->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }

        if ($domain->type === 'primary') {
            return redirect()->back()->with('error', 'Domain utama tidak dapat dihapus secara langsung. Hapus website jika ingin menghapus domain utama.');
        }

        $domainName = $domain->domain;
        $website = $domain->website;

        $domain->delete();

        // Sync Nginx VirtualHost server_name
        if ($website) {
            $this->syncNginxDomainAliases($website);
        }

        AuditLogger::log('domain_deleted', "Domain alias {$domainName} berhasil dihapus dari sistem.", auth()->id());

        return redirect()->back()->with('success', "Domain alias {$domainName} berhasil dihapus!");
    }

    /**
     * Sync all domain aliases into Nginx VirtualHost server_name directive.
     */
    protected function syncNginxDomainAliases(Website $website): void
    {
        $allDomains = $website->domains()->pluck('domain')->toArray();
        $primaryDomain = $website->domain_name;

        // Build server_name alias string
        $aliases = [$primaryDomain];
        if (! str_starts_with($primaryDomain, 'www.') && count(explode('.', $primaryDomain)) <= 2) {
            $aliases[] = "www.{$primaryDomain}";
        }

        foreach ($allDomains as $d) {
            if (! in_array($d, $aliases)) {
                $aliases[] = $d;
                if (! str_starts_with($d, 'www.') && count(explode('.', $d)) <= 2) {
                    $aliases[] = "www.{$d}";
                }
            }
        }

        $serverNameAliasString = implode(' ', array_unique($aliases));

        if (PHP_OS_FAMILY === 'Linux') {
            $nginxPath = "/etc/nginx/sites-available/{$primaryDomain}.conf";
            if (File::exists($nginxPath)) {
                $content = File::get($nginxPath);
                $updatedContent = preg_replace('/server_name\s+[^;]+;/', "server_name {$serverNameAliasString};", $content);
                
                $stagedPath = storage_path("app/nginx/{$primaryDomain}.conf");
                File::put($stagedPath, $updatedContent);

                if (is_writable("/etc/nginx/sites-available")) {
                    File::put($nginxPath, $updatedContent);
                } else {
                    @shell_exec("sudo /bin/cp " . escapeshellarg($stagedPath) . " " . escapeshellarg($nginxPath) . " 2>&1");
                }

                $testCmd = @shell_exec("sudo /usr/sbin/nginx -t 2>&1");
                if (str_contains($testCmd, 'syntax is ok')) {
                    @shell_exec("sudo /usr/bin/systemctl reload nginx 2>&1");
                }
            }
        }
    }
}
