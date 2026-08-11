<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientApiController extends ApiController
{
    /**
     * List all client users (Admin only).
     */
    public function index(): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return $this->error('Akses khusus Admin.', 403);
        }

        $clients = User::where('role', 'client')->withCount('websites')->latest()->get();
        return $this->success($clients, 'Daftar client berhasil diambil.');
    }

    /**
     * Create a new client account.
     */
    public function store(Request $request): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return $this->error('Akses khusus Admin.', 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'max_websites' => 'nullable|integer|min:1',
        ]);

        $client = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => 'client',
            'status' => 'active',
            'max_websites' => $request->input('max_websites', 3),
        ]);

        return $this->success($client, "Akun client {$client->email} berhasil dibuat!", 201);
    }

    /**
     * Update client website quota.
     */
    public function updateQuota(Request $request, User $user): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return $this->error('Akses khusus Admin.', 403);
        }

        $request->validate([
            'max_websites' => 'required|integer|min:1',
        ]);

        $user->update([
            'max_websites' => $request->max_websites,
        ]);

        return $this->success($user, "Quota website client {$user->name} berhasil diperbarui menjadi {$user->max_websites}.");
    }
}
