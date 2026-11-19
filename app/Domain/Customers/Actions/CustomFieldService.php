<?php

namespace App\Domain\Customers\Actions;

use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Domain\Customers\Models\CustomFieldValue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Custom field tanımı ve değer yönetimi (ADR-0004, Prompt 04).
 * - Tip allowlist uygulanır.
 * - Tip değişikliği: uyumsuz mevcut değer varsa istek reddedilir (sessiz bozulma yok).
 * - Değer yazımı typed kolonlara yapılır; required ve options validation sunucuda.
 */
final class CustomFieldService
{
    /**
     * Tanım oluşturma/güncellemede tip ve metadata doğrulaması.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsertDefinition(array $data, ?CustomFieldDefinition $existing = null): CustomFieldDefinition
    {
        $type = $data['type'] ?? $existing?->type;

        if (! in_array($type, CustomFieldDefinition::TYPE_ALLOWLIST, true)) {
            throw ValidationException::withMessages([
                'type' => __('İzin verilmeyen alan tipi. Tipler: :list', ['list' => implode(', ', CustomFieldDefinition::TYPE_ALLOWLIST)]),
            ]);
        }

        $options = $data['options'] ?? $existing?->options;

        if (in_array($type, ['select', 'multi_select'], true) && (empty($options) || ! is_array($options))) {
            throw ValidationException::withMessages([
                'options' => __('select/multi_select tipleri için seçenek listesi zorunludur.'),
            ]);
        }

        // Tip değişikliğinde mevcut değerleri bozmamak için kontrol (ADR-0004).
        if ($existing !== null && $type !== $existing->type) {
            $incompatible = $this->typeChangeRequiresMigration($existing->type, $type)
                && $existing->values()->exists();

            if ($incompatible) {
                throw ValidationException::withMessages([
                    'type' => __('Bu alanda mevcut değerler var; :from → :to tip değişikliği veri dönüştürme gerektirir. Önce değerleri boşaltın veya ayrı bir migration planlayın.', ['from' => $existing->type, 'to' => $type]),
                ]);
            }
        }

        return DB::transaction(function () use ($data, $existing, $options, $type): CustomFieldDefinition {
            $payload = [
                'key' => $data['key'],
                'label' => $data['label'],
                'type' => $type,
                'required' => (bool) ($data['required'] ?? false),
                'default_value' => $data['default_value'] ?? null,
                'options' => in_array($type, ['select', 'multi_select'], true) ? $options : null,
                'searchable' => (bool) ($data['searchable'] ?? false),
                'filterable' => (bool) ($data['filterable'] ?? false),
                'reportable' => (bool) ($data['reportable'] ?? false),
                'visible_to_roles' => $data['visible_to_roles'] ?? null,
                'display_order' => (int) ($data['display_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ];

            if ($existing === null) {
                return CustomFieldDefinition::create($payload);
            }

            $existing->update($payload);

            return $existing;
        });
    }

    /**
     * Müşteriye özel alan değeri yazar (tek satır upsert, typed kolon).
     *
     * @param  array<int, mixed>  $values  definition_id => raw değer
     */
    public function syncCustomerValues(Customer $customer, array $values, ?User $actor = null): void
    {
        $definitions = CustomFieldDefinition::query()
            ->where('is_active', true)
            ->whereIn('id', array_keys($values))
            ->get()
            ->keyBy('id');

        foreach ($definitions as $definition) {
            $raw = $values[$definition->id] ?? null;

            if ($raw === null || $raw === '' || (is_array($raw) && $raw === [])) {
                if ($definition->required) {
                    throw ValidationException::withMessages([
                        'custom_fields' => __(':label alanı zorunludur.', ['label' => $definition->label]),
                    ]);
                }

                CustomFieldValue::query()
                    ->where('definition_id', $definition->id)
                    ->where('customer_id', $customer->id)
                    ->delete();

                continue;
            }

            $this->assertValueMatchesType($definition, $raw);

            $columns = $this->columnsFor($definition->type, $raw);

            CustomFieldValue::query()->updateOrCreate(
                ['definition_id' => $definition->id, 'customer_id' => $customer->id],
                $columns,
            );
        }
    }

    /**
     * Değerin tip kurallarına uygunluğunu sunucu tarafında zorlar.
     */
    public function assertValueMatchesType(CustomFieldDefinition $definition, mixed $raw): void
    {
        $type = $definition->type;

        $valid = match ($type) {
            'integer' => is_int($raw),
            'decimal' => is_numeric($raw),
            'date' => is_string($raw) && strtotime($raw) !== false,
            'boolean' => is_bool($raw) || in_array($raw, [0, 1, '0', '1', true, false], true),
            'select' => is_string($raw) && in_array($raw, $definition->options ?? [], true),
            'multi_select' => is_array($raw) && collect($raw)->every(fn ($item) => in_array($item, $definition->options ?? [], true)),
            default => is_string($raw),
        };

        if (! $valid) {
            throw ValidationException::withMessages([
                'custom_fields' => __(':label alanına :type tipine uygun olmayan değer girildi.', ['label' => $definition->label, 'type' => $type]),
            ]);
        }
    }

    /**
     * Tipe göre hangi typed kolona yazılacağı.
     *
     * @return array<string, mixed>
     */
    private function columnsFor(string $type, mixed $raw): array
    {
        // Önce tüm kolonları temizle (tip uyumu garantisi)
        $base = [
            'value_text' => null,
            'value_number' => null,
            'value_date' => null,
            'value_boolean' => null,
            'value_json' => null,
        ];

        // array_merge: boş kolonlar üzerine yazılır; union (+) dolu anahtarı yoksayar.
        return match ($type) {
            'integer', 'decimal' => array_merge($base, ['value_number' => $raw]),
            'date' => array_merge($base, ['value_date' => $raw]),
            'boolean' => array_merge($base, ['value_boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN)]),
            'multi_select' => array_merge($base, ['value_json' => array_values($raw)]),
            default => array_merge($base, ['value_text' => $raw]),
        };
    }

    /**
     * Tip değişikliği için veri dönüştürmesi gerekiyor mu?
     */
    private function typeChangeRequiresMigration(string $from, string $to): bool
    {
        $textGroup = ['text', 'textarea', 'select'];
        $numberGroup = ['integer', 'decimal'];

        if (in_array($from, $textGroup, true) && in_array($to, $textGroup, true)) {
            return false; // aynı kolonda uyumlu
        }

        if (in_array($from, $numberGroup, true) && in_array($to, $numberGroup, true)) {
            return false;
        }

        return true;
    }
}
