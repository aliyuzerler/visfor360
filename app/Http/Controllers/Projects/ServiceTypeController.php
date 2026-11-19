<?php

namespace App\Http\Controllers\Projects;

use App\Domain\Audit\AuditLogger;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\ServiceType;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServiceTypeController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', ServiceType::class);

        return Inertia::render('settings/services/index', [
            'serviceTypes' => ServiceType::query()
                ->with(['pipeline:id,label,is_example', 'department:id,name'])
                ->orderBy('label')
                ->get(),
            'pipelines' => Pipeline::query()->with('stages')->orderBy('label')->get(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', ServiceType::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:service_types,key'],
            'label' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'pipeline_id' => ['required', Rule::exists('pipelines', 'id')],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
        ]);

        $service = ServiceType::create($validated + ['is_active' => true]);
        app(AuditLogger::class)->logModelChange('created', $service);

        return back()->with('success', 'Hizmet tanımı oluşturuldu.');
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $this->authorize('manage', ServiceType::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('service_types', 'key')->ignore($serviceType->id)],
            'label' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'pipeline_id' => ['required', Rule::exists('pipelines', 'id')],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $original = $serviceType->getAttributes();
        $serviceType->update($validated);
        app(AuditLogger::class)->logModelChange('updated', $serviceType, $original);

        return back()->with('success', 'Hizmet tanımı güncellendi.');
    }
}
