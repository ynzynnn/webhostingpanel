<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $apiKeys = $user->isAdmin()
            ? ApiKey::with('user')->latest()->get()
            : ApiKey::where('user_id', $user->id)->latest()->get();

        return view('api-keys.index', compact('apiKeys'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $rawKey = 'septa_' . Str::random(40);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'key' => $rawKey,
        ]);

        AuditLogger::log('api_key_created', "API Key '{$apiKey->name}' berhasil dibuat.", $user->id);

        return back()->with('success', "API Key '{$apiKey->name}' berhasil dibuat! Key: {$rawKey}");
    }

    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isClient() && $apiKey->user_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        $name = $apiKey->name;
        $apiKey->delete();

        AuditLogger::log('api_key_deleted', "API Key '{$name}' telah dihapus.", $user->id);

        return back()->with('success', "API Key '{$name}' telah dihapus.");
    }
}
