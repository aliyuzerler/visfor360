<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kullanıcı-departman üyeliği. Bir kullanıcı çok departmanlı olabilir;
 * is_manager bayrağı departman yöneticisi kapsamını belirler.
 */
class DepartmentUser extends Model
{
    protected $table = 'department_user';

    public $timestamps = false;

    protected $fillable = ['user_id', 'department_id', 'is_manager', 'joined_at'];

    protected function casts(): array
    {
        return [
            'is_manager' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
