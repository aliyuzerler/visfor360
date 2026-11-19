<?php

namespace App\Domain\Projects\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pipeline şablonu. is_example=true → müşteri onayı bekleyen ÖRNEK şablon
 * (SRS dışından aşama adları gerçek kabul edilmez).
 *
 * @property Collection<int, PipelineStage> $stages
 */
class Pipeline extends Model
{
    use SoftDeletes;

    protected $fillable = ['key', 'label', 'is_example', 'is_active'];

    protected function casts(): array
    {
        return ['is_example' => 'boolean', 'is_active' => 'boolean'];
    }

    /**
     * @return HasMany<PipelineStage, $this>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }
}
