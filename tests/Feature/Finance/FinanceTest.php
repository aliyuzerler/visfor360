<?php

namespace Tests\Feature\Finance;

use App\Domain\Finance\Actions\FinanceCalculationService;
use App\Domain\Finance\Models\FinanceSetting;
use App\Domain\Finance\Models\FinancialCategory;
use App\Domain\Finance\Models\FinancialEntry;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private FinancialCategory $incomeCategory;

    private FinancialCategory $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);

        $this->incomeCategory = FinancialCategory::create(['key' => 'hizmet-geliri', 'label' => 'Hizmet Geliri', 'direction' => 'income', 'is_example' => true]);
        $this->expenseCategory = FinancialCategory::create(['key' => 'personel-gideri', 'label' => 'Personel Gideri', 'direction' => 'expense', 'is_example' => true]);
    }

    private function entry(array $attributes = []): FinancialEntry
    {
        return FinancialEntry::create($attributes + [
            'direction' => 'income',
            'entry_type' => 'actual',
            'category_id' => $this->incomeCategory->id,
            'amount' => '1000.0000',
            'currency_code' => 'TRY',
            'entry_date' => '2026-11-01',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
            'created_by' => $this->admin->id,
        ]);
    }

    #[Test]
    public function decimal_precision_kayiptan_saklar(): void
    {
        $entry = $this->entry(['amount' => '1234.5678']);

        $this->assertSame('1234.5678', $entry->refresh()->amount);
        $this->assertDatabaseHas('financial_entries', ['id' => $entry->id, 'amount' => '1234.5678', 'currency_code' => 'TRY']);
    }

    #[Test]
    public function tahmini_ve_gerceklesen_filtro_ayri_calisir(): void
    {
        $this->entry(['entry_type' => 'estimated', 'status' => 'draft']);
        $actual = $this->entry(['amount' => '2500.0000']);

        $estimatedList = FinancialEntry::query()->where('entry_type', 'estimated')->pluck('id')->all();
        $actualList = FinancialEntry::query()->where('entry_type', 'actual')->pluck('id')->all();

        $this->assertContains($actual->id, $actualList);
        $this->assertNotContains($actual->id, $estimatedList);
        $this->assertCount(1, $estimatedList);

        // Ekranda tip filtresi çalışır
        $this->get('/app/finance?type=estimated')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('entries.data', 1));
    }

    #[Test]
    public function pnl_ve_nakit_ayri_rapor_ekranlari(): void
    {
        $this->get('/app/finance/reports/pnl')->assertOk();
        $this->get('/app/finance/reports/cash')->assertOk();
    }

    #[Test]
    public function formul_onaysiz_hesaplanmaz_needs_configuration(): void
    {
        $this->entry(['amount' => '99999.0000']);
        $this->entry(['amount' => '500.0000', 'direction' => 'expense', 'category_id' => $this->expenseCategory->id]);

        $calculation = app(FinanceCalculationService::class);

        // P&L onaysız → sayı üretilmez
        $summary = $calculation->pnlSummary();
        $this->assertSame('needs_configuration', $summary['status']);
        $this->assertNull($summary['total_income']);
        $this->assertNull($summary['net']);

        // Nakit onaysız → runway null
        $snapshot = $calculation->cashSnapshot($this->admin);
        $this->assertSame('needs_configuration', $snapshot->status);
        $this->assertNull($snapshot->cash_balance);
        $this->assertNull($snapshot->runway_months);
    }

    #[Test]
    public function onayli_demo_formul_fixture_iyle_dogru_hesaplar(): void
    {
        FinanceSetting::put('pnl_formula_approved', ['approved' => true]);
        FinanceSetting::put('cash_formula_approved', ['approved' => true]);
        FinanceSetting::put('cash_formula_config', ['starting_cash' => '10000', 'burn_lookback_months' => 1, 'forecast_horizon_months' => 12]);

        // Bu ay: 5000 gelir, 3000 gider (onaylı, gerçekleşen)
        $this->entry(['amount' => '5000.0000', 'entry_date' => now()->toDateString()]);
        $this->entry(['amount' => '3000.0000', 'direction' => 'expense', 'category_id' => $this->expenseCategory->id, 'entry_date' => now()->toDateString()]);

        $calculation = app(FinanceCalculationService::class);

        $summary = $calculation->pnlSummary();
        $this->assertSame('calculated', $summary['status']);
        $this->assertSame(5000.0, $summary['total_income']);
        $this->assertSame(3000.0, $summary['total_expense']);
        $this->assertSame(2000.0, $summary['net']);

        $snapshot = $calculation->cashSnapshot($this->admin);
        $this->assertSame('calculated', $snapshot->status);
        $this->assertSame('12000.0000', $snapshot->cash_balance); // 10000 + 5000 - 3000
        $this->assertSame('3000.0000', $snapshot->monthly_burn); // lookback 1 ay
        $this->assertSame('4.00', $snapshot->runway_months); // 12000/3000

        // Varsayımlar açıkça saklanıyor
        $this->assertSame('10000', (string) $snapshot->assumptions['starting_cash']);
        $this->assertSame(1, $snapshot->assumptions['burn_lookback_months']);
    }

    #[Test]
    public function izinsiz_kullanici_finans_verilerine_erisemez(): void
    {
        $plain = User::factory()->create(); // hiçbir izin yok
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/finance')->assertForbidden();
        $this->get('/app/finance/reports/pnl')->assertForbidden();
        $this->get('/app/finance/reports/cash')->assertForbidden();
        $this->post('/app/finance', ['amount' => '1'])->assertForbidden();
    }

    #[Test]
    public function duzeltme_eski_kaydi_iptal_edip_yeni_kaydi_zincirler(): void
    {
        $entry = $this->entry(['amount' => '1000.0000', 'status' => 'approved']);

        $this->put('/app/finance/'.$entry->id, [
            'direction' => 'income',
            'entry_type' => 'actual',
            'category_id' => $this->incomeCategory->id,
            'amount' => '1500.0000',
            'currency_code' => 'TRY',
            'entry_date' => '2026-11-01',
        ])->assertRedirect();

        $entry->refresh();
        $this->assertSame('cancelled', $entry->status); // eski kayıt korundu

        $correction = FinancialEntry::query()->where('parent_entry_id', $entry->id)->firstOrFail();
        $this->assertSame('1500.0000', $correction->amount);
        $this->assertContains($correction->status, ['draft', 'approved']); // yeni kayit durumu

        // Düzeltme audit'i
        $this->assertTrue(AuditEvent::query()->where('auditable_id', $entry->id)->where('action', 'updated')->exists());
    }

    #[Test]
    public function eszamanli_guncellemeler_kayip_olmadan_audit_uretir(): void
    {
        $entry = $this->entry(['amount' => '1000.0000']);

        $this->put('/app/finance/'.$entry->id, [
            'direction' => 'income', 'entry_type' => 'actual', 'category_id' => $this->incomeCategory->id,
            'amount' => '1200.0000', 'currency_code' => 'TRY', 'entry_date' => '2026-11-01',
        ])->assertRedirect();

        $this->put('/app/finance/'.$entry->id, [
            'direction' => 'income', 'entry_type' => 'actual', 'category_id' => $this->incomeCategory->id,
            'amount' => '1300.0000', 'currency_code' => 'TRY', 'entry_date' => '2026-11-01',
        ])->assertRedirect();

        // Kayıp update yok: son değer geçerli
        $this->assertSame('1300.0000', $entry->refresh()->amount);
        // Her güncelleme auditli (draft old. için düzeltme değil, update)
        $auditCount = AuditEvent::query()->where('action', 'updated')->where('auditable_id', $entry->id)->count();
        $this->assertGreaterThanOrEqual(2, $auditCount, 'Her guncelleme audit uretmeli');
    }

    #[Test]
    public function tarih_araligi_ve_para_birimi_filtreleri(): void
    {
        $inRange = $this->entry(['entry_date' => '2026-11-05', 'currency_code' => 'TRY']);
        $outRange = $this->entry(['entry_date' => '2027-02-01', 'currency_code' => 'TRY']);
        $usd = $this->entry(['entry_date' => '2026-11-06', 'currency_code' => 'USD']);

        $list = FinancialEntry::query()
            ->whereBetween('entry_date', ['2026-11-01', '2026-11-30'])
            ->where('currency_code', 'TRY')
            ->pluck('id')
            ->all();

        $this->assertContains($inRange->id, $list);
        $this->assertNotContains($outRange->id, $list);
        $this->assertNotContains($usd->id, $list);

        $this->get('/app/finance?from=2026-11-01&to=2026-11-30')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('entries.data', 2)); // TRY + USD, aralık içinde
    }

    #[Test]
    public function onay_akisi_ayri_izne_bagli(): void
    {
        $entry = $this->entry(['status' => 'draft']);

        // finance.approve izni olmayan yönetici onaylayamaz
        $limited = User::factory()->create();
        $limited->roles()->attach(Role::query()->where('key', 'danisman-personel')->first()); // finance izinleri yok
        $this->post('/logout');
        $this->post('/login', ['email' => $limited->email, 'password' => 'password']);

        $this->patch('/app/finance/'.$entry->id.'/approve')->assertForbidden();

        // Admin onaylar
        $this->post('/logout');
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);
        $this->patch('/app/finance/'.$entry->id.'/approve')->assertRedirect();

        $entry->refresh();
        $this->assertSame('approved', $entry->status);
        $this->assertNotNull($entry->approved_by);
        $this->assertTrue(AuditEvent::query()->where('action', 'approved')->where('auditable_id', $entry->id)->exists());
    }
}
