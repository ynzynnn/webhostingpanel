<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Domain;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainApiController extends ApiController
{
    /**
     * List mapped domains.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $domains = $user->isAdmin()
            ? Domain::with(['website', 'user'])->latest()->get()
            : Domain::where('user_id', $user->id)->with('website')->latest()->get();

        return $this->success($domains, 'Daftar domain berhasil diambil.');
    }

    /**
     * Bind a domain alias or subdomain to a website.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'domain' => 'required|string',
            'type' => 'required|in:alias,subdomain',
        ]);

        $user = auth()->user();
        $website = Website::findOrFail($request->website_id);

        if ($user->isClient() && $website->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        $cleanDomain = strtolower(trim($request->domain));
        if (Domain::where('domain', $cleanDomain)->exists()) {
            return $this->error("Domain {$cleanDomain} sudah terdaftar pada sistem.", 400);
        }

        $domainModel = Domain::create([
            'user_id' => $user->id,
            'website_id' => $website->id,
            'domain' => $cleanDomain,
            'type' => $request->type,
            'dns_status' => 'valid',
        ]);

        return $this->success($domainModel, "Domain {$cleanDomain} berhasil di-binding ke website {$website->domain_name}!", 201);
    }

    /**
     * Delete mapped domain alias.
     */
    public function destroy(Domain $domain): JsonResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $domain->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        if ($domain->type === 'primary') {
            return $this->error('Domain utama tidak dapat dihapus melalui API domain.', 400);
        }

        $domainName = $domain->domain;
        $domain->delete();

        return $this->success(null, "Domain alias {$domainName} berhasil dihapus.");
    }
}
