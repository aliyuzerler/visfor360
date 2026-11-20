<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Actions\DocumentService;
use App\Domain\Documents\Models\DocumentType;
use App\Domain\Portal\Models\PortalDocumentRequest;
use App\Domain\Portal\Models\PortalUser;
use App\Domain\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Portal anasayfa: YALNIZ aktif üyelik firmalarının projeleri + belge
 * talepleri. İç veri (finans, fırsat, iç notlar) hiç üretilmez (Prompt 15).
 */
class PortalDashboardController extends Controller
{
    public function dashboard(): Response
    {
        /** @var PortalUser $user */
        $user = Auth::guard('portal')->user();

        $customerIds = $user->activeMemberships()->pluck('customer_id')->all();

        // Customer scope mandatory: tüm sorgular üyelik firmalarıyla kısıtlı
        $projects = Project::query()
            ->whereIn('customer_id', $customerIds)
            ->whereNull('archived_at')
            ->with(['serviceType:id,label', 'currentStage:id,label'])
            ->orderBy('name')
            ->get();

        $requests = PortalDocumentRequest::query()
            ->whereIn('customer_id', $customerIds)
            ->with(['documentType:id,label', 'customer:id,name'])
            ->orderByDesc('id')
            ->get();

        $projectRows = [];
        foreach ($projects as $project) {
            $projectRows[] = [
                'id' => $project->id,
                'name' => $project->name,
                'service' => $project->serviceType->label ?? '—',
                'stage' => $project->currentStage->label ?? '—',
                'start_date' => $project->start_date?->format('d.m.Y') ?? '',
                'due_date' => $project->due_date?->format('d.m.Y') ?? '',
            ];
        }

        return Inertia::render('portal/dashboard', [
            'projects' => $projectRows,
            'documentRequests' => $requests->map(fn (PortalDocumentRequest $request) => [
                'id' => $request->id,
                'customer_name' => $request->customer?->name,
                'document_type' => $request->documentType?->label,
                'note' => $request->note,
                'status' => $request->status,
            ]),
        ]);
    }

    /** Portal belge yükleme: yalnız üyelik firmasının AÇIK talebine. */
    public function uploadDocument(Request $request): RedirectResponse
    {
        /** @var PortalUser $user */
        $user = Auth::guard('portal')->user();

        $validated = $request->validate([
            'request_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpeg,png'],
        ]);

        /** @var PortalDocumentRequest|null $documentRequest */
        $documentRequest = PortalDocumentRequest::query()
            ->where('id', $request->integer('request_id'))
            ->whereIn('customer_id', $user->activeMemberships()->pluck('customer_id')->all())
            ->where('status', 'requested')
            ->first();

        // IDOR koruması: başka firmanın talebi = 404 (varlık ifşası yok)
        if ($documentRequest === null) {
            abort(404);
        }

        $document = app(DocumentService::class)->upload(
            Customer::query()->findOrFail($documentRequest->customer_id),
            DocumentType::query()->findOrFail($documentRequest->document_type_id),
            $request->file('file'),
            null, // portal yüklemesi — iç aktör yok; inceleme kararı ADR-0014
        );

        $documentRequest->update([
            'status' => 'uploaded',
            'document_id' => $document->id,
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        return back()->with('success', 'Belge alındı; incelenecek.');
    }
}
