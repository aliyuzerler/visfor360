<?php

namespace App\Domain\Opportunities\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Checkups\Models\CheckupResult;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Opportunities\Models\OpportunityHistory;
use App\Domain\Opportunities\Models\RoadmapItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Fırsat akışı (Prompt 12, ADR-0011):
 * - Check-Up önerisi → RoadmapItem (pending_review) + Opportunity (review_pending).
 *   İnsan incelemesi yol haritasında yapılır; otomatik proje/görev YOK.
 * - Karar (approve/reject/defer): mutabakat notu ZORUNLU (boş checkbox kabul
 *   edilmez); actor/time/not history + audit.
 * - Mükerrer: aynı müşteri+hizmette aktif fırsat varsa is_duplicate işaretlenir
 *   (karar müşteriye ait; engelleme yapılmaz — ADR-0011 notu).
 */
final class OpportunityService
{
    public function importFromCheckup(CheckupResult $result, ?User $actor): array
    {
        $session = $result->session;
        $created = [];

        return DB::transaction(function () use ($result, $session, $actor, &$created): array {
            foreach ($result->recommendations as $recommendation) {
                // RoadmapItem: gelişim yol haritası kalemi (inceleme bekler)
                $roadmapItem = RoadmapItem::firstOrCreate([
                    'session_id' => $session->id,
                    'service_key' => $recommendation['service_key'],
                ], [
                    'customer_id' => $session->customer_id,
                    'label' => $recommendation['label'],
                    'detection_reason' => implode(' | ', $recommendation['reasons'] ?? []),
                    'priority' => $recommendation['priority'] ?? 100,
                    'status' => 'pending_review',
                ]);

                // Mükerrer: aynı müşteri+hizmette aktif fırsat
                $existing = Opportunity::query()
                    ->where('customer_id', $session->customer_id)
                    ->where('service_key', $recommendation['service_key'])
                    ->whereNotIn('status', ['rejected'])
                    ->first();

                $opportunity = Opportunity::create([
                    'customer_id' => $session->customer_id,
                    'service_key' => $recommendation['service_key'],
                    'title' => $recommendation['label'],
                    'source_session_id' => $session->id,
                    'roadmap_item_id' => $roadmapItem->id,
                    'detection_reason' => implode(' | ', $recommendation['reasons'] ?? []),
                    'status' => 'review_pending',
                    'is_duplicate' => $existing !== null,
                    'duplicate_of_id' => $existing?->id,
                    'created_by' => $actor?->id,
                ]);

                $roadmapItem->update(['opportunity_id' => $opportunity->id]);

                OpportunityHistory::create([
                    'opportunity_id' => $opportunity->id,
                    'field' => 'status',
                    'old_value' => null,
                    'new_value' => 'review_pending',
                    'note' => $existing !== null ? 'Mükerrer işaretli: aynı müşteri+hizmet için aktif fırsat mevcut (karar müşteriye ait).' : null,
                    'actor_id' => $actor?->id,
                    'actor_name' => $actor?->name,
                    'changed_at' => now(),
                ]);

                app(AuditLogger::class)->logModelChange('created', $opportunity);

                $created[] = $opportunity;
            }

            return $created;
        });
    }

    /**
     * Karar: approve/reject/defer. Mutabakat notu ZORUNLU.
     *
     * @param  string  $status  allowed: approved|rejected|deferred
     */
    public function decide(Opportunity $opportunity, string $status, string $discussionNote, string $decisionNote, ?User $actor): Opportunity
    {
        $allowedStatuses = ['approved', 'rejected', 'deferred'];
        if (! in_array($status, $allowedStatuses, true)) {
            throw ValidationException::withMessages(['status' => __('Geçersiz fırsat kararı.')]);
        }

        if (trim($discussionNote) === '') {
            throw ValidationException::withMessages(['discussion_note' => __('Müşteri mutabakat notu zorunludur; boş onay kabul edilmez.')]);
        }

        if ($status !== 'approved' && trim($decisionNote) === '') {
            throw ValidationException::withMessages(['decision_note' => __('Ret/erteleme için gerekçe zorunludur.')]);
        }

        return DB::transaction(function () use ($opportunity, $status, $discussionNote, $decisionNote, $actor): Opportunity {
            $old = $opportunity->status;

            $opportunity->update([
                'status' => $status,
                'discussion_note' => $discussionNote,
                'decision_note' => $decisionNote,
                'decided_by' => $actor?->id,
                'decided_at' => now(),
            ]);

            OpportunityHistory::create([
                'opportunity_id' => $opportunity->id,
                'field' => 'status',
                'old_value' => $old,
                'new_value' => $status,
                'note' => $decisionNote,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'changed_at' => now(),
            ]);

            if ($opportunity->roadmap_item_id !== null) {
                RoadmapItem::query()
                    ->where('id', $opportunity->roadmap_item_id)
                    ->update(['status' => $status === 'approved' ? 'converted' : 'pending_review']);
            }

            app(AuditLogger::class)->log('opportunity_decision', $opportunity, ['from' => $old], [
                'to' => $status,
                'discussion_note' => $discussionNote,
                'decided_by' => $actor?->id,
            ]);

            return $opportunity;
        });
    }
}
