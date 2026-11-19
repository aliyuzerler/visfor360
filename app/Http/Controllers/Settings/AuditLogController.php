<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Audit log görüntüleyici: salt-okunur, sayfalı, filtreli.
 * Bu ekran hiçbir yazma aksiyonu sunmaz; append-only DB trigger'ıyla da korunur.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AuditEvent::class);

        $events = AuditEvent::query()
            ->with(['actor:id,name,email', 'auditable'])
            ->when($request->string('action')->toString(), fn ($q, string $action) => $q->where('action', $action))
            ->when($request->string('actor')->toString(), function ($q, string $actor): void {
                $q->whereHas('actor', fn ($a) => $a->where('email', 'ilike', "%{$actor}%"));
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('settings/audit-logs/index', [
            'events' => $events,
            'filters' => [
                'action' => $request->string('action')->toString(),
                'actor' => $request->string('actor')->toString(),
            ],
        ]);
    }
}
