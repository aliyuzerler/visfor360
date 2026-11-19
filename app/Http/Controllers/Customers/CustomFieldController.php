<?php

namespace App\Http\Controllers\Customers;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Actions\CustomFieldService;
use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Custom field builder (admin ekranı). Tüm işlemler auditli;
 * tip allowlist ve tip-değişikliği koruması CustomFieldService'te.
 */
class CustomFieldController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', CustomFieldDefinition::class);

        return Inertia::render('settings/custom-fields/index', [
            'definitions' => CustomFieldDefinition::query()
                ->withCount('values')
                ->orderBy('display_order')
                ->orderBy('id')
                ->get(),
            'types' => CustomFieldDefinition::TYPE_ALLOWLIST,
        ]);
    }

    public function store(Request $request, CustomFieldService $service): RedirectResponse
    {
        $this->authorize('create', CustomFieldDefinition::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:custom_field_definitions,key'],
            'label' => ['required', 'string', 'max:160'],
            'type' => ['required', 'string', Rule::in(CustomFieldDefinition::TYPE_ALLOWLIST)],
            'required' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string', 'max:500'],
            'options' => ['nullable', 'array', 'max:100'],
            'options.*' => ['string', 'max:160'],
            'searchable' => ['nullable', 'boolean'],
            'filterable' => ['nullable', 'boolean'],
            'reportable' => ['nullable', 'boolean'],
            'visible_to_roles' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $definition = $service->upsertDefinition($validated + ['created_by' => $request->user()->id]);

        app(AuditLogger::class)->logModelChange('created', $definition);

        return back()->with('success', 'Özel alan tanımı oluşturuldu.');
    }

    public function update(Request $request, CustomFieldDefinition $definition, CustomFieldService $service): RedirectResponse
    {
        $this->authorize('update', $definition);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('custom_field_definitions', 'key')->ignore($definition->id)],
            'label' => ['required', 'string', 'max:160'],
            'type' => ['required', 'string', Rule::in(CustomFieldDefinition::TYPE_ALLOWLIST)],
            'required' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string', 'max:500'],
            'options' => ['nullable', 'array', 'max:100'],
            'options.*' => ['string', 'max:160'],
            'searchable' => ['nullable', 'boolean'],
            'filterable' => ['nullable', 'boolean'],
            'reportable' => ['nullable', 'boolean'],
            'visible_to_roles' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->upsertDefinition($validated, $definition);

        app(AuditLogger::class)->logModelChange('updated', $definition);

        return back()->with('success', 'Özel alan tanımı güncellendi.');
    }

    /**
     * Deactivate: değerler korunur; alan formlardan ve listelerden kalkar.
     */
    public function deactivate(CustomFieldDefinition $definition): RedirectResponse
    {
        $this->authorize('archive', $definition);

        $original = $definition->getAttributes();
        $definition->update(['is_active' => false]);

        app(AuditLogger::class)->logModelChange('deactivated', $definition, $original);

        return back()->with('success', 'Alan devre dışı bırakıldı; mevcut değerler korunuyor.');
    }
}
