<?php

namespace App\Http\Controllers\Documents;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Actions\DocumentService;
use App\Domain\Documents\Models\Document;
use App\Domain\Documents\Models\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(Request $request, Customer $customer, DocumentService $service): RedirectResponse
    {
        $this->authorize('upload', [Document::class, $customer]);

        $validated = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'file' => ['required', 'file', 'max:10240'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $type = DocumentType::query()->where('is_active', true)->findOrFail($validated['document_type_id']);

        $service->upload(
            $customer,
            $type,
            $request->file('file'),
            $request->user(),
            isset($validated['valid_until']) ? Carbon::parse($validated['valid_until']) : null,
        );

        return back()->with('success', 'Evrak yüklendi.');
    }

    /**
     * Güvenli indirme: private diskten stream; URL değil, policy'li route.
     */
    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        app(AuditLogger::class)->log('document.downloaded', $document);

        return Storage::disk('local')->download($document->storage_key, $document->original_name);
    }

    public function archive(Document $document): RedirectResponse
    {
        $this->authorize('archive', $document);

        $original = $document->getAttributes();
        $document->delete();

        app(AuditLogger::class)->logModelChange('archived', $document, $original);

        return back()->with('success', 'Evrak kaydı arşivlendi.');
    }
}
