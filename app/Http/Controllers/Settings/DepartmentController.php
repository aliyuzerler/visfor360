<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreDepartmentRequest;
use App\Http\Requests\Settings\UpdateDepartmentRequest;
use App\Domain\Audit\AuditLogger;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Department::class);

        return Inertia::render('settings/departments/index', [
            'departments' => Department::query()
                ->withCount('memberships')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $department = Department::create($request->validated());
        app(AuditLogger::class)->logModelChange('created', $department);

        return redirect()->route('settings.departments.index')
            ->with('success', 'Departman oluşturuldu.');
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $original = $department->getAttributes();
        $department->update($request->validated());
        app(AuditLogger::class)->logModelChange('updated', $department, $original);

        return redirect()->route('settings.departments.index')
            ->with('success', 'Departman güncellendi.');
    }

    /**
     * Silme yerine arşivleme (is_active=false) + soft delete yok; kayıt geçmişi korunur.
     */
    public function archive(Department $department): RedirectResponse
    {
        $this->authorize('archive', $department);

        $original = $department->getAttributes();
        $department->update(['is_active' => false]);
        app(AuditLogger::class)->logModelChange('archived', $department, $original);

        return redirect()->route('settings.departments.index')
            ->with('success', 'Departman arşivlendi (kayıt geçmişi korunur).');
    }
}
