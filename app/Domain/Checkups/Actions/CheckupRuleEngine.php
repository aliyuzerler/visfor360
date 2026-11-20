<?php

namespace App\Domain\Checkups\Actions;

/**
 * Deklaratif kural motoru (Prompt 11).
 * - Girdi: rules (json snapshot), answers (key => value)
 * - Group'lar OR; group içi koşullar AND.
 * - Operator allowlist: eval/dynamic execution YOK — koşullar saf karşılaştırma.
 * - Missing answer → koşul sağlanmaz (false); invalid answer → koşul false +
 *   izde 'invalid_answer' notu; hiçbir koşul exception üretmez.
 * - Deterministik: priority asc, sonra id asc; aynı girdi → aynı sonuç.
 */
final class CheckupRuleEngine
{
    public const ENGINE_VERSION = 'declarative.v1';

    /** Allowlist: dışarıdan gelen operator bu liste dışındaysa koşul false olur. */
    public const OPERATOR_ALLOWLIST = ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'not_in', 'is_true', 'is_false'];

    /**
     * @param  list<array<string, mixed>>  $rules
     * @param  array<string, mixed>  $answers
     * @return array{recommendations: list<array<string, mixed>>, trace: list<array<string, mixed>>}
     */
    public function evaluate(array $rules, array $answers): array
    {
        $recommendations = [];
        $trace = [];

        foreach ($rules as $rule) {
            if (($rule['is_active'] ?? true) !== true) {
                $trace[] = ['rule_id' => $rule['id'] ?? null, 'label' => $rule['label'] ?? null, 'matched' => false, 'reason' => 'inactive'];

                continue;
            }

            $outcome = $this->evaluateGroups($rule['condition_groups'] ?? [], $answers);

            $trace[] = [
                'rule_id' => $rule['id'] ?? null,
                'label' => $rule['label'] ?? null,
                'matched' => $outcome['matched'],
                'failed_conditions' => $outcome['failed'],
                'invalid_answers' => $outcome['invalid'],
            ];

            if ($outcome['matched']) {
                $recommendations[] = [
                    'service_key' => (string) $rule['recommendation_service_key'],
                    'label' => (string) $rule['recommendation_label'],
                    'matched_rule_id' => $rule['id'] ?? null,
                    'matched_rule_label' => $rule['label'] ?? null,
                    'priority' => (int) ($rule['priority'] ?? 100),
                    'reasons' => array_map(
                        fn (array $condition): string => sprintf(
                            "'%s' sorusundaki cevabınız bu öneriyi tetikledi (kural: %s)",
                            (string) ($condition['question_key'] ?? '?'),
                            (string) ($rule['label'] ?? '?'),
                        ),
                        $outcome['matched_conditions'],
                    ),
                ];
            }
        }

        // Deterministik sıralama: priority, sonra rule id
        usort($recommendations, fn (array $a, array $b) => [$a['priority'], $a['matched_rule_id'] ?? 0] <=> [$b['priority'], $b['matched_rule_id'] ?? 0]);

        return ['recommendations' => $recommendations, 'trace' => $trace];
    }

    /**
     * @param  array<int, mixed>  $groups
     * @param  array<string, mixed>  $answers
     * @return array{matched: bool, failed: list<string>, invalid: list<string>, matched_conditions: list<array<string, mixed>>}
     */
    private function evaluateGroups(array $groups, array $answers): array
    {
        $failed = [];
        $invalid = [];
        $matchedConditions = [];

        if ($groups === []) {
            return ['matched' => false, 'failed' => ['boş koşul grubu'], 'invalid' => [], 'matched_conditions' => []];
        }

        foreach ($groups as $group) {
            $groupFailed = [];
            $groupMatched = [];
            $groupInvalid = [];

            foreach ($group['conditions'] ?? [] as $condition) {
                $questionKey = (string) ($condition['question_key'] ?? '');
                $operator = (string) ($condition['operator'] ?? '');
                $expected = $condition['value'] ?? null;

                if (! in_array($operator, self::OPERATOR_ALLOWLIST, true)) {
                    $groupFailed[] = sprintf('%s: izin dışı operator', $questionKey);
                    $groupInvalid[] = $questionKey;

                    continue;
                }

                if (! array_key_exists($questionKey, $answers) || $answers[$questionKey] === null || $answers[$questionKey] === '') {
                    $groupFailed[] = sprintf('%s: cevap eksik', $questionKey);

                    continue;
                }

                $actual = $answers[$questionKey];

                if ($this->compare($operator, $actual, $expected)) {
                    $groupMatched[] = $condition;
                } else {
                    $groupFailed[] = sprintf('%s (%s %s)', $questionKey, $operator, json_encode($expected, JSON_UNESCAPED_UNICODE));
                }
            }

            if ($groupMatched !== [] && $groupFailed === []) {
                // Group AND: tüm koşullar sağlandı
                $matchedConditions = array_merge($matchedConditions, $groupMatched);

                return ['matched' => true, 'failed' => [], 'invalid' => $groupInvalid, 'matched_conditions' => $matchedConditions];
            }

            $failed = array_merge($failed, $groupFailed);
            $invalid = array_merge($invalid, $groupInvalid);
        }

        return ['matched' => false, 'failed' => $failed, 'invalid' => $invalid, 'matched_conditions' => []];
    }

    private function compare(string $operator, mixed $actual, mixed $expected): bool
    {
        // Invalid answer koruması: tip uyuşmazlığı koşulu false yapar (exception yok)
        return match ($operator) {
            'eq' => $this->looseEqual($actual, $expected),
            'neq' => ! $this->looseEqual($actual, $expected),
            'gt', 'gte', 'lt', 'lte' => $this->numericCompare($operator, $actual, $expected),
            'in' => is_array($expected) && in_array($actual, $expected, true),
            'not_in' => is_array($expected) && ! in_array($actual, $expected, true),
            'is_true' => $actual === true || $actual === '1' || $actual === 1,
            'is_false' => $actual === false || $actual === '0' || $actual === 0 || $actual === null,
            default => false,
        };
    }

    private function looseEqual(mixed $actual, mixed $expected): bool
    {
        if (is_array($actual) && is_array($expected)) {
            sort($actual);
            sort($expected);

            return $actual === $expected;
        }

        return (string) $actual === (string) $expected;
    }

    private function numericCompare(string $operator, mixed $actual, mixed $expected): bool
    {
        if (! is_numeric($actual) || ! is_numeric($expected)) {
            return false; // sayısal olmayan cevap: koşul false, invalid sayılmaz
        }

        return match ($operator) {
            'gt' => (float) $actual > (float) $expected,
            'gte' => (float) $actual >= (float) $expected,
            'lt' => (float) $actual < (float) $expected,
            'lte' => (float) $actual <= (float) $expected,
            default => false,
        };
    }
}
