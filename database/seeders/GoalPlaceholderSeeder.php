<?php

namespace Database\Seeders;

use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Goals\Models\VisionStatement;
use Illuminate\Database\Seeder;

/**
 * Placeholder 12 hedef + vizyon (Prompt 08).
 * SINIR: gerçek hedef adları/formülü kaynak PDF'de yok — bu seeder yalnızca
 * is_example=true ile etiketlenmiş PLACEHOLDER kayıtlar üretir; üretim
 * verisi müşteri girdisiyle girilir (TEST-PROMPT-8 madde 1).
 */
class GoalPlaceholderSeeder extends Seeder
{
    public function run(): void
    {
        VisionStatement::query()->firstOrCreate(
            ['id' => 1],
            [
                'content' => '[Müşteri vizyon metni bekleniyor — 5 yıllık vizyon buraya girilecek]',
                'horizon_years' => 5,
                'is_approved' => false,
            ],
        );

        for ($slot = 1; $slot <= StrategicGoal::SLOT_COUNT; $slot++) {
            $key = 'goal-'.str_pad((string) $slot, 2, '0', STR_PAD_LEFT);

            StrategicGoal::query()->firstOrCreate(
                ['slot_key' => $key],
                [
                    'label' => null, // müşteri kararı
                    'description' => null,
                    'is_example' => true,
                    'sort_order' => $slot,
                ],
            );
        }
    }
}
