<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Models\Website;
use App\Services\SslService;
use App\Services\WebsiteProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebsiteApiController extends ApiController
{
    public function __construct(
        protected WebsiteProvisioningService $provisioningService,
        protected SslService $sslService
    ) {}

    /**
     * List websites (Admin sees all, Client sees own).
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $websites = $user->isAdmin()
            ? Website::with('user')->latest()->get()
            : Website::where('user_id', $user->id)->latest()->get();

        return $this->success($websites, 'Daftar website berhasil diambil.');
    }

    /**
     * Provision a new website.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'domain_name' => 'required|string',
            'php_version' => 'nullable|string|in:8.1,8.2,8.3,8.4',
            'client_email' => 'nullable|email',
            'enable_auto_ssl' => 'nullable|boolean',
        ]);

        $user = auth()->user();

        // If client_email specified and caller is admin, assign to that client
        if ($request->filled('client_email') && $user->isAdmin()) {
            $targetUser = User::where('email', $request->client_email)->first();
            if (! $targetUser) {
                return $this->error("Client dengan email {$request->client_email} tidak ditemukan.", 404);
            }
        } else {
            $targetUser = $user;
        }

        // Quota check
        if ($targetUser->hasReachedWebsiteQuota()) {
            return $this->error("Client {$targetUser->name} telah mencapai batas maksimal quota website ({$targetUser->max_websites}).", 422);
        }

        $phpVer = $request->input('php_version', '8.3');
        $autoSsl = $request->boolean('enable_auto_ssl', true);

        $result = $this->provisioningService->createWebsite(
            $targetUser,
            $request->domain_name,
            $phpVer,
            $autoSsl
        );

        if (! $result['success']) {
            return $this->error($result['message'], 400);
        }

        return $this->success($result['website'], $result['message'], 201);
    }

    /**
     * Show single website details.
     */
    public function show(Website $website): JsonResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $website->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        $website->load(['user', 'domains', 'databases', 'sslCertificate']);
        return $this->success($website, 'Detail website berhasil diambil.');
    }

    /**
     * Toggle suspend website status.
     */
    public function toggleSuspend(Website $website): JsonResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $website->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        $website->status = $website->status === 'suspended' ? 'active' : 'suspended';
        $website->save();

        return $this->success($website, "Status website berhasil diubah menjadi {$website->status}.");
    }

    /**
     * Issue SSL certificate for website.
     */
    public function issueSsl(Website $website): JsonResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $website->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        $result = $this->sslService->issueSslForWebsite($website);

        return $result['success']
            ? $this->success($result, $result['message'])
            : $this->error($result['message'], 400);
    }

    /**
     * Delete website.
     */
    public function destroy(Website $website): JsonResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $website->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        $domain = $website->domain_name;
        $website->delete();

        return $this->success(null, "Website {$domain} berhasil dihapus.");
    }
}
