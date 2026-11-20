<?php

namespace App\Http\Middleware;

use App\Domain\Api\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API auth (Prompt 16): Bearer token → hash lookup → aktif mi + scope var mı.
 * Hatalı/eksik → 401; scope yok → 403; tutarlı JSON hata gövdesi.
 */
class EnsureApiToken
{
    public function handle(Request $request, Closure $next, ?string $requiredScope = null): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer === null || $bearer === '') {
            return response()->json(['error' => ['code' => 'unauthenticated', 'message' => 'API token gerekli.']], 401);
        }

        $token = ApiToken::query()->where('token_hash', ApiToken::hash($bearer))->first();

        if ($token === null || ! $token->isValid()) {
            return response()->json(['error' => ['code' => 'invalid_token', 'message' => 'Token geçersiz, süresi dolmuş veya iptal edilmiş.']], 401);
        }

        if ($requiredScope !== null && ! $token->hasScope($requiredScope)) {
            return response()->json(['error' => ['code' => 'forbidden_scope', 'message' => 'Token bu kapsama sahip değil.']], 403);
        }

        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
