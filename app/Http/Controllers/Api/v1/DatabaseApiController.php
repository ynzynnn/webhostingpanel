<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\DatabaseModel;
use App\Models\Website;
use App\Services\DatabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatabaseApiController extends ApiController
{
    public function __construct(
        protected DatabaseService $databaseService
    ) {}

    /**
     * List databases.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $databases = $user->isAdmin()
            ? DatabaseModel::with(['website', 'user'])->latest()->get()
            : DatabaseModel::where('user_id', $user->id)->with('website')->latest()->get();

        return $this->success($databases, 'Daftar database berhasil diambil.');
    }

    /**
     * Create MariaDB database & user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'db_name' => 'required|string|max:20|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:8',
            'website_id' => 'nullable|exists:websites,id',
        ]);

        $user = auth()->user();
        $websiteId = $request->filled('website_id') ? $request->website_id : null;

        if ($websiteId && $user->isClient()) {
            $website = Website::findOrFail($websiteId);
            if ($website->user_id !== $user->id) {
                return $this->error('Akses tidak sah.', 403);
            }
        }

        $result = $this->databaseService->createDatabase($user, $request->db_name, $request->password, $websiteId);

        return $result['success']
            ? $this->success($result['database'], $result['message'], 201)
            : $this->error($result['message'], 400);
    }

    /**
     * Delete MariaDB database.
     */
    public function destroy(DatabaseModel $database): JsonResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $database->user_id !== $user->id) {
            return $this->error('Akses tidak sah.', 403);
        }

        $result = $this->databaseService->deleteDatabase($database);

        return $result['success']
            ? $this->success(null, $result['message'])
            : $this->error($result['message'], 400);
    }
}
