<?php

use App\Domain\Api\Actions\WebhookService;
use App\Http\Controllers\Api\V1\CustomerApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// REST API /api/v1 (Prompt 16, ADR-0015): versioned, validated, paginated,
// rate-limited (throttle:api 60/min), consistent JSON errors.
Route::prefix('v1')->middleware(['throttle:api'])->group(function (): void {
    Route::get('/openapi.json', function (): JsonResponse {
        return response()->json(json_decode(file_get_contents(public_path('api/openapi.json')), true));
    })->name('api.openapi');

    // Test sink: yalnız testing — webhook imza/replay doğrulama demo (Prompt 16)
    if (app()->environment('testing')) {
        Route::post('/test-sink', function (Request $request): JsonResponse {
            $signature = (string) $request->header('X-Visfor360-Signature');
            $timestamp = (string) $request->header('X-Visfor360-Timestamp');
            $secret = (string) $request->header('X-Test-Secret', '');
            $body = $request->getContent();
            $service = app(WebhookService::class);

            return response()->json([
                'verified' => $service->verifySignature($secret, $timestamp, $body, $signature),
                'event' => $request->json('event'),
                'payload' => $request->json('payload'),
            ]);
        });
    }

    Route::middleware(['api.token:customers.read'])->group(function (): void {
        Route::get('/customers', [CustomerApiController::class, 'index'])->name('api.customers.index');
        Route::get('/customers/{customer}', [CustomerApiController::class, 'show'])->name('api.customers.show');
    });
});
