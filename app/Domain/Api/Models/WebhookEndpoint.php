<?php

namespace App\Domain\Api\Models;

use App\Domain\Audit\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Webhook endpoint: secret hash + müşteri onaylı event listesi.
 *
 * @property Carbon|null $rotated_at
 */
class WebhookEndpoint extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'url', 'secret_hash', 'enabled_events', 'is_active', 'rotated_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled_events' => 'array',
            'is_active' => 'boolean',
            'rotated_at' => 'datetime',
        ];
    }
}
