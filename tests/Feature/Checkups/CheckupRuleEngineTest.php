<?php

namespace Tests\Feature\Checkups;

use App\Domain\Checkups\Actions\CheckupRuleEngine;
use App\Domain\Checkups\Actions\CheckupService;
use App\Domain\Checkups\Models\CheckupQuestion;
use App\Domain\Checkups\Models\CheckupRule;
use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Domain\Customers\Models\Customer;
use App\Domain\Projects\Models\Project;
use App\Domain\Tasks\Models\Task;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckupRuleEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);
    }

    /**
     * ÖRNEK prototip şablon (müşteri soru seti bekleniyor — ADR-0010).
     * Kurallar fixture; üretim hizmet önerisi değildir.
     */
    private function createExampleTemplate(): CheckupTemplate
    {
        $template = CheckupTemplate::create(['key' => 'dijital-olgunluk-ornek', 'title' => 'Dijital Olgunluk (ÖRNEK)', 'status' => 'published']);

        CheckupQuestion::create(['template_id' => $template->id, 'key' => 'erp_kullanimi', 'label' => 'ERP kullanıyor musunuz?', 'type' => 'boolean', 'sort_order' => 1]);
        CheckupQuestion::create(['template_id' => $template->id, 'key' => 'cihaz_sayisi', 'label' => 'Yönetilen cihaz sayısı', 'type' => 'number', 'required' => true, 'sort_order' => 2, 'config' => ['min' => 0, 'max' => 100000]]);
        CheckupQuestion::create(['template_id' => $template->id, 'key' => 'sektor', 'label' => 'Sektör', 'type' => 'single_choice', 'required' => true, 'sort_order' => 3, 'config' => ['options' => ['üretim', 'turizm', 'perakende']]]);
        CheckupQuestion::create(['template_id' => $template->id, 'key' => 'kesinti_toleransi', 'label' => 'Kesinti toleransı (dk/ay)', 'type' => 'number', 'sort_order' => 4]);

        CheckupRule::create([
            'template_id' => $template->id,
            'label' => 'ERP entegrasyon ihtiyacı',
            'priority' => 10,
            'condition_groups' => [
                // Group 1: AND
                ['conditions' => [['question_key' => 'erp_kullanimi', 'operator' => 'is_false']]],
            ],
            'recommendation_service_key' => 'sv_erp',
            'recommendation_label' => 'ERP Danışmanlığı (ÖRNEK)',
        ]);

        CheckupRule::create([
            'template_id' => $template->id,
            'label' => 'Yüksek cihaz sayısı — altyapı değerlendirmesi',
            'priority' => 20,
            'condition_groups' => [
                // OR: iki bağımsız group
                ['conditions' => [['question_key' => 'cihaz_sayisi', 'operator' => 'gte', 'value' => 50]]],
                ['conditions' => [['question_key' => 'kesinti_toleransi', 'operator' => 'lte', 'value' => 10]]],
            ],
            'recommendation_service_key' => 'sv_altyapi',
            'recommendation_label' => 'Altyapı Değerlendirmesi (ÖRNEK)',
        ]);

        CheckupRule::create([
            'template_id' => $template->id,
            'label' => 'Turizm sektörü paketi',
            'priority' => 30,
            'condition_groups' => [
                ['conditions' => [['question_key' => 'sektor', 'operator' => 'eq', 'value' => 'turizm']]],
            ],
            'recommendation_service_key' => 'sv_turizm',
            'recommendation_label' => 'Turizm Dijital Paketi (ÖRNEK)',
        ]);

        return $template;
    }

    #[Test]
    public function motor_and_or_esik_null_invalid_ve_oncelik_dogru_calisir(): void
    {
        $engine = new CheckupRuleEngine;
        $template = $this->createExampleTemplate();
        $rules = $template->rules()->orderBy('priority')->get()->map(fn ($rule) => $rule->only(['id', 'label', 'priority', 'condition_groups', 'recommendation_service_key', 'recommendation_label', 'is_active']))->all();

        // 1) OR grupları: cihaz 60 (gte 50) → altyapı önerisi; ERP yok → false
        $out = $engine->evaluate($rules, ['cihaz_sayisi' => 60, 'sektor' => 'üretim', 'erp_kullanimi' => 1]);
        $keys = array_column($out['recommendations'], 'service_key');
        $this->assertContains('sv_altyapi', $keys);
        $this->assertNotContains('sv_erp', $keys);

        // 2) Eşik sınır: gte 50 → 49 false, 50 true
        $out49 = $engine->evaluate($rules, ['cihaz_sayisi' => 49, 'sektor' => 'üretim']);
        $this->assertNotContains('sv_altyapi', array_column($out49['recommendations'], 'service_key'));
        $out50 = $engine->evaluate($rules, ['cihaz_sayisi' => 50, 'sektor' => 'üretim']);
        $this->assertContains('sv_altyapi', array_column($out50['recommendations'], 'service_key'));

        // 3) Missing answer: kesinti_toleransi yok → lte grubu false; invalid: number alanına metin
        $outMissing = $engine->evaluate($rules, ['sektor' => 'üretim']);
        $traceAltyapi = array_values(array_filter($outMissing['trace'], fn ($t) => $t['label'] === 'Yüksek cihaz sayısı — altyapı değerlendirmesi'))[0];
        $this->assertFalse($traceAltyapi['matched']);
        $this->assertNotEmpty($traceAltyapi['failed_conditions']);

        // 4) Priority: ERP(10) + altyapı(20) + turizm(30) hepsi eşleşirse sıra priority'ye göre
        $outAll = $engine->evaluate($rules, ['cihaz_sayisi' => 80, 'sektor' => 'turizm', 'erp_kullanimi' => 0, 'kesinti_toleransi' => 5]);
        $this->assertSame(['sv_erp', 'sv_altyapi', 'sv_turizm'], array_column($outAll['recommendations'], 'service_key'));
    }

    #[Test]
    public function izin_disi_operator_kosulu_false_yapar_exception_uretmez(): void
    {
        $engine = new CheckupRuleEngine;
        $rules = [[
            'id' => 1, 'label' => 'Yasak koşul', 'priority' => 1, 'is_active' => true,
            'condition_groups' => [['conditions' => [['question_key' => 'x', 'operator' => 'eval', 'value' => 'evil()']]]],
            'recommendation_service_key' => 'sv_x', 'recommendation_label' => 'X',
        ]];

        $out = $engine->evaluate($rules, ['x' => 'anything']);
        $this->assertSame([], $out['recommendations']);
        $this->assertFalse($out['trace'][0]['matched']);
        $this->assertContains('x', $out['trace'][0]['invalid_answers']);
    }

    #[Test]
    public function tamamlanan_oturum_snapshot_ile_sonucu_korur(): void
    {
        $template = $this->createExampleTemplate();
        $customer = Customer::create(['name' => 'Checkup Firması']);

        $this->post('/app/checkups', ['template_id' => $template->id, 'customer_id' => $customer->id])->assertRedirect();
        $session = CheckupSession::query()->firstOrFail();
        $this->assertSame($template->version, $session->template_version);

        $answers = ['cihaz_sayisi' => 60, 'sektor' => 'turizm', 'erp_kullani mi' => 0];
        $answers['erp_kullanimi'] = 0;
        unset($answers['erp_kullani mi']);
        $this->patch('/app/checkups/'.$session->id.'/complete', ['answers' => $answers])->assertRedirect();

        $result = $session->refresh()->results()->firstOrFail();
        $this->assertSame(['sv_erp', 'sv_altyapi', 'sv_turizm'], array_column($result->recommendations, 'service_key'));

        // Şablon + kurallar SONRADAN değiştirilir (versiyon ilerletilir)
        $template->update(['version' => $template->version + 1, 'title' => 'Yeni başlık']);
        CheckupRule::create([
            'template_id' => $template->id,
            'label' => 'Yeni kural (herkese öneri)',
            'priority' => 1,
            'condition_groups' => [['conditions' => [['question_key' => 'sektor', 'operator' => 'not_in', 'value' => ['yok']]]]],
            'recommendation_service_key' => 'sv_yeni',
            'recommendation_label' => 'Yeni Hizmet (ÖRNEK)',
        ]);

        // Eski sonuç DEĞİŞMEZ (snapshot korunur)
        $this->assertSame(['sv_erp', 'sv_altyapi', 'sv_turizm'], array_column($result->refresh()->recommendations, 'service_key'));
        $this->assertSame(1, $session->template_version); // oturumun kilidi aynı
    }

    #[Test]
    public function ayni_cevap_ayni_surum_tekrar_calisirligindeterministik(): void
    {
        $template = $this->createExampleTemplate();
        $customer = Customer::create(['name' => 'Det Firma']);

        $service = app(CheckupService::class);
        $answers = ['cihaz_sayisi' => 70, 'sektor' => 'perakende', 'erp_kullanimi' => 0, 'kesinti_toleransi' => 8];

        $sessionA = CheckupSession::create(['template_id' => $template->id, 'template_version' => $template->version, 'customer_id' => $customer->id, 'started_by' => $this->admin->id]);
        $sessionB = CheckupSession::create(['template_id' => $template->id, 'template_version' => $template->version, 'customer_id' => $customer->id, 'started_by' => $this->admin->id]);

        $resultA = $service->complete($sessionA, $answers, $this->admin);
        $resultB = $service->complete($sessionB, $answers, $this->admin);

        $this->assertSame($resultA->deterministic_hash, $resultB->deterministic_hash);
        $this->assertSame($resultA->recommendations, $resultB->recommendations);
    }

    #[Test]
    public function tamamlanma_audit_yazar_ve_proje_gorev_olusturmaz(): void
    {
        $template = $this->createExampleTemplate();
        $customer = Customer::create(['name' => 'Audit Firma']);

        $this->post('/app/checkups', ['template_id' => $template->id, 'customer_id' => $customer->id])->assertRedirect();
        $session = CheckupSession::query()->firstOrFail();
        $this->patch('/app/checkups/'.$session->id.'/complete', ['answers' => ['cihaz_sayisi' => 60, 'sektor' => 'turizm', 'erp_kullanimi' => 0]])->assertRedirect();

        $this->assertTrue(AuditEvent::query()->where('action', 'checkup_completed')->where('auditable_id', $session->id)->exists());

        // SINIR: yalnız fırsat ÖNERİSİ — proje/task ÜRETİLMEZ
        $this->assertSame(0, Project::query()->count());
        $this->assertSame(0, Task::query()->count());
    }

    #[Test]
    public function zorunlu_soru_eksikse_tamamlama_reddedilir(): void
    {
        $template = $this->createExampleTemplate();
        $customer = Customer::create(['name' => 'Eksik Firma']);

        $this->post('/app/checkups', ['template_id' => $template->id, 'customer_id' => $customer->id])->assertRedirect();
        $session = CheckupSession::query()->firstOrFail();

        $this->from(route('checkups.show', $session))
            ->patch('/app/checkups/'.$session->id.'/complete', ['answers' => ['erp_kullanimi' => 0]])
            ->assertRedirect(route('checkups.show', $session))
            ->assertSessionHasErrors('answers');

        $this->assertSame('draft', $session->refresh()->status);
    }

    #[Test]
    public function izinsiz_kullanici_checkup_ekranlarina_giremez(): void
    {
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/checkups')->assertForbidden();
        $this->get('/app/settings/checkups')->assertForbidden();
    }
}
