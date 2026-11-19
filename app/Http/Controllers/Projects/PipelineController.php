<?php

namespace App\Http\Controllers\Projects;

use App\Domain\Audit\AuditLogger;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\PipelineStage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PipelineController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', Pipeline::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:pipelines,key'],
            'label' => ['required', 'string', 'max:160'],
        ]);

        $pipeline = Pipeline::create($validated + ['is_example' => true, 'is_active' => true]);
        app(AuditLogger::class)->logModelChange('created', $pipeline);

        return back()->with('success', 'Pipeline şablonu oluşturuldu (örnek olarak etiketlendi).');
    }

    public function storeStage(Request $request, Pipeline $pipeline): RedirectResponse
    {
        $this->authorize('manage', Pipeline::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('pipeline_stages', 'key')->where(fn ($q) => $q->where('pipeline_id', $pipeline->id))],
            'label' => ['required', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_terminal' => ['nullable', 'boolean'],
            'required_fields' => ['nullable', 'array', 'max:20'],
            'required_fields.*' => ['string', 'max:60'],
            'required_document_type_id' => ['nullable', Rule::exists('document_types', 'id')],
        ]);

        $stage = $pipeline->stages()->create([
            'key' => $validated['key'],
            'label' => $validated['label'],
            'sort_order' => $validated['sort_order'] ?? ($pipeline->stages()->count() + 1),
            'is_active' => true,
            'is_terminal' => (bool) ($validated['is_terminal'] ?? false),
            'required_fields' => $validated['required_fields'] ?? null,
            'required_document_type_id' => $validated['required_document_type_id'] ?? null,
        ]);
        app(AuditLogger::class)->logModelChange('created', $stage);

        return back()->with('success', 'Aşama eklendi.');
    }

    public function updateStage(Request $request, PipelineStage $stage): RedirectResponse
    {
        $this->authorize('manage', Pipeline::class);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:160'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'is_terminal' => ['required', 'boolean'],
            'required_fields' => ['nullable', 'array', 'max:20'],
            'required_fields.*' => ['string', 'max:60'],
            'required_document_type_id' => ['nullable', Rule::exists('document_types', 'id')],
        ]);

        $original = $stage->getAttributes();
        $stage->update($validated);
        app(AuditLogger::class)->logModelChange('updated', $stage, $original);

        return back()->with('success', 'Aşama güncellendi.');
    }
}
