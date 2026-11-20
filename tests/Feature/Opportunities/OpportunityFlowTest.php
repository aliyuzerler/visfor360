<?php

namespace Tests\Feature\Opportunities;

use App\Domain\Checkups\Actions\CheckupService;
use App\Domain\Checkups\Models\CheckupQuestion;
use App\Domain\Checkups\Models\CheckupRule;
use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Domain\Customers\Models\Customer;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Opportunities\Models\OpportunityHistory;
use App\Domain\Opportunities\Models\RoadmapItem;
use App\Domain\Projects\Models\Project;
use App\Domain\Tasks\Models\Task;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private CheckupTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);

        // ÖRNEK prototip şablon + tek kural
        $this->template = CheckupTemplate::create(['key' => 'opp-template', 'title' => 'ÖRNEK Şablon', 'status' => 'published']);
        CheckupQuestion::create(['template_id' => $this->template->id, 'key' => 'cihaz_sayisi', 'label' => 'Cihaz sayısı', 'type' => 'number', 'required' => true, 'sort_order' => 1]);
        CheckupRule::create([
            'template_id' => $this->template->id,
            'label' => 'Altyapı önerisi',
            'priority' => 10,
            'condition_groups' => [['conditions' => [['question_key' => 'cihaz_sayisi', 'operator' => 'gte', 'value' => 50]]]],
            'recommendation_service_key' => 'sv_altyapi',
            'recommendation_label' => 'Altyapı Değerlendirmesi (ÖRNEK)',
        ]);
    }

    private function completedSession(Customer $customer): CheckupSession
    {
        $session = CheckupSession::create([
            'template_id' => $this->template->id,
            'template_version' => $this->template->version,
            'customer_id' => $customer->id,
            'started_by' => $this->admin->id,
        ]);
        app(CheckupService::class)->complete($session, ['cihaz_sayisi' => 80], $this->admin);

        return $session;
    }

    #[Test]
    public function checkup_tespiti_dogru_musteri_ve_hizmete_bagli_firsat_uretir(): void
    {
        $customer = Customer::create(['name' => 'Fırsat Firması']);
        $session = $this->completedSession($customer);
        $result = $session->results()->firstOrFail();

        $this->post('/app/checkups/'.$result->id.'/import')->assertRedirect();

        $opportunity = Opportunity::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('sv_altyapi', $opportunity->service_key);
        $this->assertSame($session->id, $opportunity->source_session_id);
        $this->assertSame('review_pending', $opportunity->status);

        // RoadmapItem da oluştu ve fırsata bağlandı
        $roadmapItem = RoadmapItem::query()->where('session_id', $session->id)->firstOrFail();
        $this->assertSame('pending_review', $roadmapItem->status);
        $this->assertSame($opportunity->id, $roadmapItem->opportunity_id);
        $this->assertStringContainsString('cihaz_sayisi', $opportunity->detection_reason ?? '');
    }

    #[Test]
    public function mutabakat_notu_olmadan_karar_gecerli_olmaz(): void
    {
        $customer = Customer::create(['name' => 'Mutabakat Firması']);
        $session = $this->completedSession($customer);
        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();
        $opportunity = Opportunity::query()->firstOrFail();

        // Boş not → reddedilir
        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'approved',
            'discussion_note' => '',
        ])->assertSessionHasErrors('discussion_note');

        $this->assertSame('review_pending', $opportunity->refresh()->status);

        // Not ile onay geçer
        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'approved',
            'discussion_note' => 'Müşteriyle telefon görüşmesi yapıldı, ihtiyaç onaylandı.',
        ])->assertRedirect();

        $opportunity->refresh();
        $this->assertSame('approved', $opportunity->status);
        $this->assertNotNull($opportunity->decided_at);
        $this->assertSame($this->admin->id, $opportunity->decided_by);
    }

    #[Test]
    public function ret_ve_erteleme_gerekcesi_zorunlu_ve_auditli(): void
    {
        $customer = Customer::create(['name' => 'Ret Firması']);
        $session = $this->completedSession($customer);
        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();
        $opportunity = Opportunity::query()->firstOrFail();

        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'rejected',
            'discussion_note' => 'Müşteri bu yıl bütçe planlamıyor.',
            'decision_note' => '',
        ])->assertSessionHasErrors('decision_note');

        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'rejected',
            'discussion_note' => 'Müşteriyle görüşüldü.',
            'decision_note' => 'Bütçe onayı 2027 yılına ertelendi.',
        ])->assertRedirect();

        $opportunity->refresh();
        $this->assertSame('rejected', $opportunity->status);

        // History + audit
        $this->assertTrue(
            OpportunityHistory::query()->where('opportunity_id', $opportunity->id)->where('new_value', 'rejected')->exists()
        );
        $this->assertTrue(
            AuditEvent::query()->where('action', 'opportunity_decision')->where('auditable_id', $opportunity->id)->exists()
        );
    }

    #[Test]
    public function karara_izni_olmayan_kullanici_onaylayamaz(): void
    {
        $customer = Customer::create(['name' => 'İzin Firması']);
        $session = $this->completedSession($customer);
        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();
        $opportunity = Opportunity::query()->firstOrFail();

        // opportunities.approve izni olmayan satış sorumlusu: karar veremez
        $sales = User::factory()->create();
        $sales->roles()->attach(Role::query()->where('key', 'satis-sorumlusu')->first());
        $this->post('/logout');
        $this->post('/login', ['email' => $sales->email, 'password' => 'password']);

        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'approved',
            'discussion_note' => 'Kendim onaylıyorum.',
        ])->assertForbidden();

        $this->assertSame('review_pending', $opportunity->refresh()->status);
    }

    #[Test]
    public function insan_onayi_olmadan_proje_ve_gorev_olusmaz(): void
    {
        $customer = Customer::create(['name' => 'Dönüşüm Firması']);
        $session = $this->completedSession($customer);
        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();

        // Fırsat ONAYLANMAMIŞ durumda
        $this->assertSame(0, Project::query()->count());
        $this->assertSame(0, Task::query()->count());

        // Onaylansa bile Prompt 12'de otomatik dönüşüm yok (Prompt 13 akışı)
        $opportunity = Opportunity::query()->firstOrFail();
        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'approved',
            'discussion_note' => 'Onay verildi; proje dönüşümü ayrı akışla yapılır.',
        ])->assertRedirect();

        $this->assertSame(0, Project::query()->count());
        $this->assertSame(0, Task::query()->count());
    }

    #[Test]
    public function mukerrer_tespit_isaretlenir_engellenmez(): void
    {
        $customer = Customer::create(['name' => 'Mükerrer Firması']);

        // İki ayrı oturum, aynı hizmet önerisi
        $sessionA = $this->completedSession($customer);
        $this->post('/app/checkups/'.$sessionA->results()->firstOrFail()->id.'/import')->assertRedirect();
        $sessionB = $this->completedSession($customer);
        $this->post('/app/checkups/'.$sessionB->results()->firstOrFail()->id.'/import')->assertRedirect();

        $opportunities = Opportunity::query()->where('customer_id', $customer->id)->orderBy('id')->get();
        $this->assertSame(2, $opportunities->count());

        $this->assertFalse($opportunities->first()->is_duplicate);
        $this->assertTrue($opportunities->last()->is_duplicate);
        $this->assertSame($opportunities->first()->id, $opportunities->last()->duplicate_of_id);
    }

    #[Test]
    public function firsat_listesi_filtre_ve_sayfalama_uygular(): void
    {
        foreach (['A Firması', 'B Firması'] as $name) {
            $customer = Customer::create(['name' => $name]);
            $this->post('/app/checkups/'.$this->completedSession($customer)->results()->firstOrFail()->id.'/import')->assertRedirect();
        }

        $this->get('/app/opportunities?status=review_pending')->assertOk();
        $this->get('/app/opportunities?search=A')->assertOk();
        $this->get('/app/opportunities?duplicates=1')->assertOk();

        // İzinsiz kullanıcı
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);
        $this->get('/app/opportunities')->assertForbidden();
    }
}
