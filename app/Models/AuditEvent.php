<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

/**
 * Append-only audit kaydı (Prompt 03).
 * UPDATE ve DELETE model seviyesinde RuntimeException üretir; PostgreSQL'de
 * ayrıca veritabanı trigger'ı ile zorlanır (migration'a bakın).
 * Hassas alanlar AuditLogger tarafından yazılmadan maskeLenir.
 */
/**
 * @property Model|null $auditable
 */
class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id', 'actor_name', 'action', 'auditable_type', 'auditable_id',
        'before', 'after', 'ip_address', 'user_agent', 'correlation_id', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Audit kayıtları güncellenemez (append-only).');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Audit kayıtları silinemez (append-only).');
        });
    }
}
