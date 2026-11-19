<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Sağlık kontrolü: konfigürasyon/stack bilgisi sızdırmayan minimal yanıt.
Route::get('/health', HealthController::class)->name('health');

Route::get('/', function () {
    return inertia('Welcome');
});
