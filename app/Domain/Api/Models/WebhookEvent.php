<?php

namespace App\Domain\Api\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Webhook event: uuid unique (idempotency/replay).
 *
 * @property Carbon $occurred_at
 */
class WebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['event_uuid', 'event_name', 'payload', 'occurred_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<WebhookDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'event_id');
    }
}
