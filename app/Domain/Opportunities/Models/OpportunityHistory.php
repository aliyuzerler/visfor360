<?php

namespace App\Domain\Opportunities\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fırsat kararı geçmişi: status/alan değişiklikleri + aktör + mutabakat notu.
 *
 * @property Opportunity $opportunity
 */
class OpportunityHistory extends Model
{
    protected $table = 'opportunity_history';

    public $timestamps = false;

    protected $fillable = [
        'opportunity_id', 'field', 'old_value', 'new_value', 'note',
        'actor_id', 'actor_name', 'changed_at',
    ];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }
}
