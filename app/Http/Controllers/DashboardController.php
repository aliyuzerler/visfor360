<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Genel Bakış ekranı. Faz 1 (Prompt 10) sonunda gerçek KPI/uyarı verisine bağlanır;
 * o noktaya kadar sahte sayı gösterilmez, boş durumlar sunulur.
 */
class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('dashboard');
    }
}
