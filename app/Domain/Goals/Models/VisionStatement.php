<?php

namespace App\Domain\Goals\Models;

use App\Domain\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * 5 yıllık vizyon beyanı. İçerik müşteri girdisi; is_approved=false ise
 * ekranlarda "onay bekliyor" etiketiyle görünür.
 */
class VisionStatement extends Model
{
    use Auditable;

    protected $fillable = ['content', 'horizon_years', 'is_approved', 'version', 'created_by'];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }
}
