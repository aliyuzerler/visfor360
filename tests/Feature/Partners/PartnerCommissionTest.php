<?php

namespace Tests\Feature\Partners;

use App\Domain\Customers\Models\Customer;
use App\Domain\Partners\Actions\CommissionService;
use App\Domain\Partners\Models\Partner;
use App\Domain\Partners\Models\PartnerAgreement;
use App\Domain\Partners\Models\PartnerWork;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PartnerCommissionTest extends TestCase
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

    private function createPartner(): Partner
    {
        return Partner::create(['name' => 'Mimarlık Bürosu', 'expertise_type' => 'mimarluk', 'created_by' => $this->admin->id]);
    }

    private function createAgreement(Partner $partner, string $type, string $rate, string $status = 'draft'): PartnerAgreement
    {
        $agreement = PartnerAgreement::create([
            'partner_id' => $partner->id,
            'commission_type' => $type,
            'commission_rate' => $rate,
            'currency_code' => 'TRY',
            'status' => $status,
        ]);

        if ($status === 'approved') {
            $agreement->update(['approved_by' => $this->admin->id, 'approved_at' => now()]);
        }

        return $agreement;
    }

    private function createWork(Partner $partner, string $value): PartnerWork
    {
        $customer = Customer::create(['name' => 'Ortak Firması']);

        return PartnerWork::create([
            'partner_id' => $partner->id,
            'customer_id' => $customer->id,
            'title' => 'Yönlendirilen proje',
            'referral_type' => 'referral',
            'value' => $value,
            'currency_code' => 'TRY',
            'created_by' => $this->admin->id,
        ]);
    }

    #[Test]
    public function onaysiz_agreement_ile_komisyon_hesabi_engellenir(): void
    {
        $partner = $this->createPartner();
        $this->createAgreement($partner, 'percent_of_value', '10', 'draft'); // onaysız
        $work = $this->createWork($partner, '50000');

        $this->expectException(ValidationException::class);
        app(CommissionService::class)->calculate($work, $this->admin);
    }

    #[Test]
    public function komisyon_formulu_fixture_ornekleriyle_birebir_eslesir(): void
    {
        // Örnek hesap 1: yüzde 10, matrah 50.000 → 5.000,00
        $partnerA = $this->createPartner();
        $this->createAgreement($partnerA, 'percent_of_value', '10', 'approved');
        $workA = $this->createWork($partnerA, '50000');
        $commissionA = app(CommissionService::class)->calculate($workA, $this->admin);
        $this->assertSame('5000.00', number_format((float) $commissionA->calculated_amount, 2, '.', ''));

        // Örnek hesap 2: sabit tutar 7.500 → 7.500,00
        $partnerB = Partner::create(['name' => 'Hukuk Bürosu', 'created_by' => $this->admin->id]);
        $this->createAgreement($partnerB, 'fixed_amount', '7500', 'approved');
        $workB = $this->createWork($partnerB, '0');
        $commissionB = app(CommissionService::class)->calculate($workB, $this->admin);
        $this->assertSame('7500.00', number_format((float) $commissionB->calculated_amount, 2, '.', ''));

        // Örnek hesap 3: yüzde 12,5, matrah 99.999,99 → 12.499,99875 → 12.500,00 (round 2)
        $partnerC = Partner::create(['name' => 'Enerji Danışmanlığı', 'created_by' => $this->admin->id]);
        $this->createAgreement($partnerC, 'percent_of_value', '12.5', 'approved');
        $workC = $this->createWork($partnerC, '99999.99');
        $commissionC = app(CommissionService::class)->calculate($workC, $this->admin);
        $this->assertSame('12500.00', number_format((float) $commissionC->calculated_amount, 2, '.', ''));

        $this->assertSame(CommissionService::ENGINE_VERSION, $commissionA->formula_version);
    }

    #[Test]
    public function decimal_ve_currency_dogru_saklanir(): void
    {
        $partner = $this->createPartner();
        $this->createAgreement($partner, 'fixed_amount', '1234.5678', 'approved');
        $work = $this->createWork($partner, '99999.9999');

        $commission = app(CommissionService::class)->calculate($work, $this->admin);

        $this->assertSame('99999.9999', $commission->base_amount);
        $this->assertSame('TRY', $commission->currency_code);
        $this->assertDatabaseHas('partner_commissions', ['id' => $commission->id, 'base_amount' => '99999.9999']);
    }

    #[Test]
    public function komisyon_onayi_izli_ve_auditli(): void
    {
        $partner = $this->createPartner();
        $this->createAgreement($partner, 'percent_of_value', '10', 'approved');
        $work = $this->createWork($partner, '50000');
        $commission = app(CommissionService::class)->calculate($work, $this->admin);

        $this->post('/app/partners/'.$partner->id.'/works/'.$work->id.'/commission')->assertRedirect();

        $commission->update(['status' => 'approved', 'approved_by' => $this->admin->id, 'approved_at' => now()]);

        $this->assertTrue(AuditEvent::query()->where('action', 'commission_calculated')->where('auditable_id', $commission->id)->exists());
        $this->assertSame('approved', $commission->refresh()->status);
    }

    #[Test]
    public function iptal_duzeltme_onceki_kaydi_korur(): void
    {
        $partner = $this->createPartner();
        $this->createAgreement($partner, 'percent_of_value', '10', 'approved');
        $work = $this->createWork($partner, '50000');
        $commission = app(CommissionService::class)->calculate($work, $this->admin);

        // Düzeltme: eski kayıt cancelled, yeni kayıt ayrı — geçmiş korunur
        $commission->update(['status' => 'cancelled']);
        $newCommission = app(CommissionService::class)->calculate($work->refresh(), $this->admin);

        $this->assertSame('cancelled', $commission->refresh()->status);
        $this->assertNotSame($commission->id, $newCommission->id);
        $this->assertSame('5000.00', number_format((float) $newCommission->calculated_amount, 2, '.', ''));
    }

    #[Test]
    public function partner_verisi_ic_kayit_portal_yok(): void
    {
        // Partner ekranları iç kullanıcılara partners izniyle açılır;
        // dış erişim/portal yok: partners izni olmayan kullanıcı 403 alır
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/partners')->assertForbidden();
        $this->post('/app/partners', ['name' => 'X'])->assertForbidden();

    }

    #[Test]
    public function ortak_is_musteri_ve_hizmet_baglantisi_dogru(): void
    {
        $partner = $this->createPartner();
        $customer = Customer::create(['name' => 'Bağlantılı Müşteri']);

        $this->post('/app/partners/'.$partner->id.'/works', [
            'title' => 'Yürütülen iş',
            'customer_id' => $customer->id,
            'referral_type' => 'work',
            'value' => '25000',
            'currency_code' => 'TRY',
        ])->assertRedirect();

        $work = PartnerWork::query()->where('partner_id', $partner->id)->firstOrFail();
        $this->assertSame($customer->id, $work->customer_id);
        $this->assertSame('work', $work->referral_type);
    }

    #[Test]
    public function izinsiz_kullanici_partner_ekranlarina_giremez(): void
    {
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/partners')->assertForbidden();
        $this->post('/app/partners', ['name' => 'X'])->assertForbidden();
    }
}
