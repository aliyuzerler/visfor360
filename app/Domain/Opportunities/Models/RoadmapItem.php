<?php

namespace App\Domain\Opportunities\Models;

use App\Domain\Checkups\Models\CheckupSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Gelişim yol haritası kalemi: Check-Up önerisinin insan incelemesi
 * bekleyen karşılığı. Fırsata çevrildikten sonra opportunity_id bağlanır.
 *
 * @property CheckupSession $session
 */
class RoadmapItem extends Model
{
    public const STATUS_ALLOWLIST = ['pending_review', 'converted', 'dismissed'];

    protected $fillable = [
        'session_id', 'customer_id', 'service_key', 'label', 'detection_reason',
        'priority', 'status', 'opportunity_id',
    ];

    /**
     * @return BelongsTo<CheckupSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CheckupSession::class, 'session_id');
    }

    /**
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }
}
