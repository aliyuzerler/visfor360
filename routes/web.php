<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlaceholderController;
use Illuminate\Support\Facades\Route;

// Sağlık kontrolü: konfigürasyon/stack bilgisi sızdırmayan minimal yanıt.
Route::get('/health', HealthController::class)->name('health');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
 * Ekran kabuğu sayfaları (docs/SCREEN_MAP.md ile eşleşir).
 * NOT: Kimlik modülü (Faz 1, Prompt 03) ile birlikte bu grubun tamamı
 * 'auth' middleware'ına alınacaktır. Placeholder sayfalar veri içermediği
 * için ara dönemde erişim riski taşımaz.
 */
Route::prefix('app')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    foreach (PlaceholderController::pageKeys() as $pageKey) {
        Route::get('/'.$pageKey, [PlaceholderController::class, 'show'])
            ->name($pageKey.'.index')
            ->defaults('page', $pageKey);
    }
});
