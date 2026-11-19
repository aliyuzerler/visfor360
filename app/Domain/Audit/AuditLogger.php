<?php

namespace App\Domain\Audit;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Merkezi audit servisi (Prompt 03).
 * - actor, zaman, entity, action ve before/after yazar.
 * - Hassas alanları (parola, token, secret) maskelemeden yazmaz.
 * - correlation_id: X-Correlation-Id başlığından ya da istek başına üretilir.
 * Audit log'a parola/cookie/secret yazılmaz (AGENTS.md kuralı).
 */
final class AuditLogger
{
    /** Audit'e asla yazılmayacak alan adları (kısmi eşleşme). */
    private const SENSITIVE_PATTERNS = [
        'password', 'remember_token', 'token', 'secret', 'authorization', 'cookie',
    ];

    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $before = null,
        ?array $after = null,
        ?User $actor = null,
    ): AuditEvent {
        $actor ??= Auth::user();

        return AuditEvent::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'system',
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'before' => $this->mask($before),
            'after' => $this->mask($after),
            'ip_address' => Request::ip(),
            'user_agent' => Str::limit((string) Request::userAgent(), 500, ''),
            'correlation_id' => $this->correlationId(),
            'created_at' => now(),
        ]);
    }

    /**
     * Model create/update/delete arasındaki farkı before/after olarak kaydeder.
     * timestamps ve hassas alanlar hariçlenir.
     */
    public function logModelChange(string $action, Model $model, ?array $original = null): AuditEvent
    {
        $attributes = $this->visibleAttributes($model);

        return $this->log(
            $action,
            $model,
            $original !== null ? $this->visibleAttributes($original, $model) : null,
            $attributes,
        );
    }

    /**
     * @param  array<string, mixed>|Model  $attributes
     * @return array<string, mixed>
     */
    private function visibleAttributes(array|Model $attributes, ?Model $reference = null): array
    {
        if ($attributes instanceof Model) {
            $attributes = $attributes->getAttributes();
        } elseif ($reference instanceof Model) {
            // update için eski değerler: eski model snapshot'ının attribute'ları
        }

        unset($attributes['updated_at'], $attributes['created_at']);

        return $attributes;
    }

    /**
     * Hassas alanları maskeleyerek güvenli çıktı üretir.
     *
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>|null
     */
    public function mask(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $masked = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitive($key)) {
                $masked[$key] = '[MASKED]';

                continue;
            }

            if (is_array($value)) {
                $masked[$key] = $this->mask($value);

                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }

    private function isSensitive(string $key): bool
    {
        foreach (self::SENSITIVE_PATTERNS as $pattern) {
            if (str_contains(strtolower($key), $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function correlationId(): string
    {
        $header = (string) Request::header('X-Correlation-Id');

        if ($header !== '' && Str::isUuid($header)) {
            return $header;
        }

        return (string) Str::uuid();
    }
}
