<?php

namespace App\Domain\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pipeline aşaması: sıralanabilir, active/inactive, terminal flag destekli.
 * required_fields: geçişte zorunlu proje alanları; required_document_type_id:
 * geçişte müşteride geçerli olması istenen evrak (Prompt 05 entegrasyonu).
 *
 * @property Pipeline $pipeline
 */
class PipelineStage extends Model
{
    protected $fillable = [
        'pipeline_id', 'key', 'label', 'sort_order', 'is_active',
        'is_terminal', 'required_fields', 'required_document_type_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_terminal' => 'boolean',
            'required_fields' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Pipeline, $this>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }
}
