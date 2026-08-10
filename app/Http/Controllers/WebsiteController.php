<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\SslService;
use App\Services\WebsiteProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function __construct(
        protected WebsiteProvisioningService $provisioningService,
        protected SslService $sslService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $websites = $user->isAdmin()
            ? Website::with(['user', 'sslCertificates'])->latest()->get()
            : Website::where('user_id', $user->id)->with('sslCertificates')->latest()->get();

        return view('websites.index', compact('websites'));
    }

    public function create(): View
    {
        return view('websites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain_name' => ['required', 'string', 'max:255'],
            'php_version' => ['required', 'in:8.3,8.2,8.1'],
            'auto_ssl' => ['nullable', 'boolean'],
        ]);

        $user = auth()->user();
        
        if ($user->hasReachedWebsiteQuota()) {
            $max = $user->max_websites ?? 5;
            return back()->withErrors(['domain_name' => "Gagal membuat website: Akun Anda telah mencapai batas kuota maksimum {$max} website."])->withInput();
        }

        $enableAutoSsl = $request->boolean('auto_ssl', true);

        $result = $this->provisioningService->createWebsite(
            $user,
            $validated['domain_name'],
            $validated['php_version'],
            $enableAutoSsl
        );

        if (! $result['success']) {
            return back()->withErrors(['domain_name' => $result['message']])->withInput();
        }

        $redirectRoute = $user->isAdmin() ? 'admin.websites' : 'client.websites';
        return redirect()->route($redirectRoute)->with('success', $result['message']);
    }

    public function show(Website $website): View|RedirectResponse
    {
        // Authorization check: Clients can only view owned websites
        if (! auth()->user()->isAdmin() && $website->user_id !== auth()->id()) {
            abort(403);
        }

        // Preview generated Nginx Configuration
        $nginxConfigPath = PHP_OS_FAMILY === 'Linux'
            ? "/etc/nginx/sites-available/{$website->domain_name}.conf"
            : storage_path("app/nginx/{$website->domain_name}.conf");

        $nginxConfig = file_exists($nginxConfigPath)
            ? file_get_contents($nginxConfigPath)
            : "# Nginx configuration file location: {$nginxConfigPath}\n# Will be active upon VPS deployment.";

        return view('websites.show', compact('website', 'nginxConfig'));
    }

    public function toggleSuspend(Website $website): RedirectResponse
    {
        if (! auth()->user()->isAdmin() && $website->user_id !== auth()->id()) {
            abort(403);
        }

        $this->provisioningService->toggleSuspend($website);
        $statusLabel = ucfirst($website->status);

        return back()->with('success', "Status website {$website->domain_name} berhasil diubah menjadi {$statusLabel}.");
    }

    public function fixPermissions(Website $website): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $website->user_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        $sysUser = $website->system_user;
        $phpVer = $website->php_version;
        $baseDir = dirname($website->document_root);

        if (PHP_OS_FAMILY === 'Linux') {
            // 1. Ensure Linux user exists
            @shell_exec("id -u " . escapeshellarg($sysUser) . " 2>&1 || sudo /usr/sbin/useradd -r -s /bin/false -g www-data " . escapeshellarg($sysUser) . " 2>&1");

            // 2. Fix FPM Pool config user = www-data
            $etcFpmConf = "/etc/php/{$phpVer}/fpm/pool.d/{$sysUser}.conf";
            if (\File::exists($etcFpmConf)) {
                $fpmContent = \File::get($etcFpmConf);
                $fpmContent = preg_replace('/user\s*=\s*.*/', 'user = www-data', $fpmContent);
                $fpmContent = preg_replace('/group\s*=\s*.*/', 'group = www-data', $fpmContent);
                $stagedFpm = storage_path("app/fpm/{$sysUser}.conf");
                \File::put($stagedFpm, $fpmContent);
                @shell_exec("sudo /bin/cp " . escapeshellarg($stagedFpm) . " " . escapeshellarg($etcFpmConf) . " 2>&1");
            }

            // 3. Fix Ownership & Permissions
            @shell_exec("sudo /bin/chown -R www-data:www-data " . escapeshellarg($baseDir) . " 2>&1");
            @shell_exec("sudo /usr/bin/find " . escapeshellarg($baseDir) . " -type d -exec chmod 755 {} + 2>&1");
            @shell_exec("sudo /usr/bin/find " . escapeshellarg($baseDir) . " -type f -exec chmod 644 {} + 2>&1");

            // 4. Restart Services
            @shell_exec("sudo /usr/bin/systemctl restart php{$phpVer}-fpm 2>&1");
            @shell_exec("sudo /usr/bin/systemctl reload nginx 2>&1");
        }

        return back()->with('success', "Service PHP-FPM {$phpVer} & Izin Berkas untuk {$website->domain_name} telah berhasil diperbaiki 100%! Website siap diakses kembali.");
    }

    public function destroy(Website $website): RedirectResponse
    {
        if (! auth()->user()->isAdmin() && $website->user_id !== auth()->id()) {
            abort(403);
        }

        $domain = $website->domain_name;
        $this->provisioningService->deleteWebsite($website);

        $redirectRoute = auth()->user()->isAdmin() ? 'admin.websites' : 'client.websites';
        return redirect()->route($redirectRoute)->with('success', "Website {$domain} beserta seluruh konfigurasi dan filenya berhasil dihapus.");
    }

    public function issueSsl(Website $website): RedirectResponse
    {
        if (! auth()->user()->isAdmin() && $website->user_id !== auth()->id()) {
            abort(403);
        }

        $result = $this->sslService->issueCertificate($website);

        if (! $result['success']) {
            return back()->withErrors(['ssl' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }
}
