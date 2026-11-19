<?php

namespace App\Domain\Audit;

use Illuminate\Database\Eloquent\Model;

/**
 * Modeller için otomatik audit hook'u (Prompt 03).
 * Kullanım: modelde `use Auditable;` — create/update/delete otomatik kaydedilir.
 * Hassas değişikliklerin (parola vs.) loglanmaması AuditLogger maskelemesiyle
 * çifte güvenceye alınır. Audit başarısızsa işlem sessizce geçmez: exception
 * yükselir (fail closed); kritik akışlarda transaction içinde çağrılır.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            app(AuditLogger::class)->logModelChange('created', $model);
        });

        static::updated(function (Model $model): void {
            $changes = $model->getChanges();

            if ($changes === []) {
                return;
            }

            $before = [];
            foreach (array_keys($changes) as $key) {
                $before[$key] = $model->getOriginal($key);
            }

            app(AuditLogger::class)->log('updated', $model, $before, $changes);
        });

        static::deleted(function (Model $model): void {
            $logger = app(AuditLogger::class);
            $logger->log('deleted', $model, $logger->mask($model->getAttributes()), null);
        });
    }
}
