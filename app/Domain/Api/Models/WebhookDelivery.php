<?php

namespace App\Domain\Api\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Teslimat kaydı: attempts, response code, failure nedeni, backoff, dead-letter.
 *
 * @property Carbon|null $next_retry_at
 * @property Carbon|null $last_attempt_at
 */
class WebhookDelivery extends Model
{
    protected $fillable = [
        'endpoint_id', 'event_id', 'attempts', 'status', 'response_code',
        'last_error', 'next_retry_at', 'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'next_retry_at' => 'datetime',
            'last_attempt_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WebhookEndpoint, $this>
     */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'endpoint_id');
    }

    /**
     * @return BelongsTo<WebhookEvent, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class, 'event_id');
    }
}
