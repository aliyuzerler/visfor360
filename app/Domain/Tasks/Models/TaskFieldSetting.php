<?php

namespace App\Domain\Tasks\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Görev alan zorunluluk ayarı (müşteri kararı ile kalıcılaşır; varsayılanlar
 * minimum: title zorunlu, geri kalan opsiyonel).
 */
class TaskFieldSetting extends Model
{
    protected $fillable = ['field', 'required', 'is_active'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'is_active' => 'boolean'];
    }

    /**
     * Alan → zorunlu mu haritası; ayar yoksa default kural.
     *
     * @return array<string, bool>
     */
    public static function requirementMap(): array
    {
        $defaults = [
            'title' => true,
            'description' => false,
            'due_date' => false,
            'project_id' => false,
            'customer_id' => false,
            'department_id' => false,
        ];

        foreach (self::query()->where('is_active', true)->get() as $setting) {
            $defaults[$setting->field] = $setting->required;
        }

        return $defaults;
    }
}
