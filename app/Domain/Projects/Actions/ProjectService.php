<?php

namespace App\Domain\Projects\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Documents\Actions\DocumentService;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStageHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Proje aşama geçiş servisi (Prompt 06).
 * Kurallar (backend'de zorlanır — drag/drop ile aşılamaz):
 * - Hedef aşama projenin hizmetinin pipeline'ından olmalı (yanlış pipeline reddi).
 * - Aşama aktif olmalı; terminal aşamadan çıkış reddedilir.
 * - Hedef aşamanın required_fields alanları projede dolu olmalı.
 * - required_document_type_id varsa müşteride geçerli belge aranır.
 * - Geçiş atomiktir; history (actor/old/new/time) + audit yazılır.
 */
final class ProjectService
{
    public function transitionStage(Project $project, PipelineStage $targetStage, ?User $actor, array $meta = []): Project
    {
        if ($project->isArchived()) {
            throw ValidationException::withMessages(['stage' => __('Arşivlenmiş projede aşama değişikliği yapılamaz.')]);
        }

        $pipelineId = $project->serviceType->pipeline_id;

        if ($targetStage->pipeline_id !== $pipelineId) {
            throw ValidationException::withMessages([
                'stage' => __('Hedef aşama, projenin hizmetine bağlı pipeline\'a ait değil.'),
            ]);
        }

        if (! $targetStage->is_active) {
            throw ValidationException::withMessages(['stage' => __('Pasif aşamaya geçiş yapılamaz.')]);
        }

        // Relation cache yerine DB'den çöz: aynı process'te arka arkaya
        // geçişlerde eski değer kullanılmasın (terminal denetimi güvenli olsun).
        $current = $project->pipeline_stage_id !== null
            ? PipelineStage::find($project->pipeline_stage_id)
            : null;

        if ($current !== null && $current->is_terminal) {
            throw ValidationException::withMessages(['stage' => __('Terminal aşamadaki proje ileri taşınamaz.')]);
        }

        if ($current !== null && $current->id === $targetStage->id) {
            throw ValidationException::withMessages(['stage' => __('Proje zaten bu aşamada.')]);
        }

        // Zorunlu alan kontrolü (hedef aşama meta)
        $missing = collect($targetStage->required_fields ?? [])
            ->filter(fn (string $field) => filled($project->{$field}) === false)
            ->values()
            ->all();

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'stage' => __('Aşağıdaki alanlar bu aşamaya geçiş için zorunlu: :fields', ['fields' => implode(', ', $missing)]),
            ]);
        }

        // Zorunlu evrak kontrolü (Prompt 05 ile entegre)
        if ($targetStage->required_document_type_id !== null) {
            $statuses = app(DocumentService::class)->statusFor($project->customer);
            $hasValid = collect($statuses)->contains(
                fn (array $row) => $row['type']->id === $targetStage->required_document_type_id
                    && in_array($row['status'], ['valid', 'expiring_soon'], true),
            );

            if (! $hasValid) {
                throw ValidationException::withMessages([
                    'stage' => __('Bu aşamaya geçiş için geçerli zorunlu evrak eksik.'),
                ]);
            }
        }

        return DB::transaction(function () use ($project, $targetStage, $actor, $meta, $current): Project {
            $project->update(['pipeline_stage_id' => $targetStage->id]);

            ProjectStageHistory::create([
                'project_id' => $project->id,
                'from_stage_id' => $current?->id,
                'to_stage_id' => $targetStage->id,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'transitioned_at' => now(),
                'meta' => $meta,
            ]);

            app(AuditLogger::class)->log('stage_changed', $project, [
                'from_stage' => $current?->label,
                'to_stage' => $targetStage->label,
            ]);

            return $project;
        });
    }
}
