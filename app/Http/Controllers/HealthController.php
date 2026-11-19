<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Minimal sağlık kontrolü; konfigürasyon, sürüm veya stack bilgisi sızdırmaz.
     */
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
            $db = 'up';
            $httpStatus = 200;
        } catch (Throwable) {
            // Görüntüleme amaçlı: ayrıntı (sürüm/psql mesajı) sızdırılmaz.
            $db = 'down';
            $httpStatus = 503;
        }

        return response()->json([
            'status' => $db === 'up' ? 'ok' : 'degraded',
            'db' => $db,
        ], $httpStatus);
    }
}
