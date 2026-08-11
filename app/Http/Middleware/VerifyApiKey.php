<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming API request using API Key authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (! $key && $request->hasHeader('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (str_starts_with($authHeader, 'Bearer ')) {
                $key = substr($authHeader, 7);
            }
        }

        if (! $key) {
            $key = $request->query('api_key');
        }

        if (! $key) {
            return response()->json([
                'success' => false,
                'message' => 'API Key diperlukan. Harap sertakan header X-API-Key atau Authorization Bearer token.',
            ], 401);
        }

        $apiKey = ApiKey::where('key', $key)->with('user')->first();

        if (! $apiKey || ! $apiKey->user) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak valid atau telah dihapus.',
            ], 401);
        }

        // Authenticate user into current request context
        Auth::setUser($apiKey->user);

        // Update last used timestamp
        $apiKey->update(['last_used_at' => now()]);

        return $next($request);
    }
}
