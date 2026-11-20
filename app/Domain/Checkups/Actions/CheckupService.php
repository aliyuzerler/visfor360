<?php

namespace App\Domain\Checkups\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Checkups\Models\CheckupQuestion;
use App\Domain\Checkups\Models\CheckupResult;
use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Check-Up oturum akışı (Prompt 11).
 * - Tamamlama anında exact snapshot: sorular + kurallar + cevaplar (madde 3).
 * - Sonuç: yalnız Opportunity ÖNERİSİ — proje/task ÜRETİLMEZ (madde 9/sınır).
 * - Deterministik hash: aynı cevap + aynı sürüm → aynı hash (madde 4).
 */
final class CheckupService
{
    public function __construct(private CheckupRuleEngine $engine) {}

    /**
     * @param  array<string, mixed>  $answers
     */
    public function complete(CheckupSession $session, array $answers, ?User $actor): CheckupResult
    {
        if ($session->status === 'completed') {
            throw ValidationException::withMessages(['session' => __('Oturum zaten tamamlandı; sonuçlar değişmez.')]);
        }

        $template = CheckupTemplate::query()->with(['questions', 'rules'])->findOrFail($session->template_id);

        // Zorunlu soru kontrolü (missing answer → hata; motor içinde false yapılır ama tamamlama bloklanır)
        $missing = $template->questions->filter(fn (CheckupQuestion $question) => $question->required && (! isset($answers[$question->key]) || $answers[$question->key] === '' || $answers[$question->key] === []));
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => __('Zorunlu sorular eksik: :keys', ['keys' => $missing->pluck('key')->implode(', ')]),
            ]);
        }

        return DB::transaction(function () use ($session, $answers, $template): CheckupResult {
            $questionsSnapshot = $template->questions->map(fn ($question) => $question->only(['key', 'label', 'type', 'required', 'sort_order', 'config']))->all();
            $rulesSnapshot = $template->rules->map(fn ($rule) => $rule->only(['id', 'label', 'priority', 'condition_groups', 'recommendation_service_key', 'recommendation_label', 'is_active']))->all();

            $session->update([
                'answers_snapshot' => $answers,
                'questions_snapshot' => $questionsSnapshot,
                'rules_snapshot' => $rulesSnapshot,
                'template_version' => $template->version,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Motor SNAPSHOT kurallarıyla çalışır (canlı kurallarla değil)
            $outcome = $this->engine->evaluate($rulesSnapshot, $answers);

            $hash = hash('sha256', serialize([
                'engine' => $this->engine::ENGINE_VERSION,
                'rules' => $rulesSnapshot,
                'answers' => $answers,
            ]));

            $result = CheckupResult::create([
                'session_id' => $session->id,
                'recommendations' => $outcome['recommendations'],
                'rule_trace' => $outcome['trace'],
                'engine_version' => $this->engine::ENGINE_VERSION,
                'deterministic_hash' => $hash,
            ]);

            app(AuditLogger::class)->log('checkup_completed', $session, null, [
                'recommendation_count' => count($outcome['recommendations']),
                'hash' => $hash,
            ]);

            return $result;
        });
    }
}
