<?php

namespace Tests\Feature\Customers;

use App\Domain\Customers\Actions\CustomFieldService;
use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Domain\Customers\Models\CustomFieldValue;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldTest extends TestCase
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

    #[Test]
    public function tanim_olusturma_audit_olayi_yazar(): void
    {
        $this->post('/app/settings/custom-fields', [
            'key' => 'faaliyet-alani',
            'label' => 'Faaliyet Alanı',
            'type' => 'text',
        ])->assertRedirect();

        $definition = CustomFieldDefinition::query()->where('key', 'faaliyet-alani')->first();
        $this->assertNotNull($definition);
        $this->assertTrue(
            AuditEvent::query()->where('action', 'created')->where('auditable_id', $definition->id)->exists()
        );
    }

    #[Test]
    public function izin_verilmeyen_tip_reddedilir(): void
    {
        $this->post('/app/settings/custom-fields', [
            'key' => 'kotu-alan',
            'label' => 'Kötü Alan',
            'type' => 'script',
        ])->assertSessionHasErrors('type');

        $this->assertNull(CustomFieldDefinition::query()->where('key', 'kotu-alan')->first());
    }

    public static function validValueProvider(): array
    {
        return [
            'text' => ['text', 'merkez'],
            'textarea' => ['textarea', "uzun\nmetin"],
            'integer' => ['integer', 42],
            'decimal' => ['decimal', 42.5],
            'date' => ['date', '2026-11-19'],
            'boolean' => ['boolean', true],
            'select' => ['select', 'A'],
            'multi_select' => ['multi_select', ['A', 'B']],
        ];
    }

    #[Test]
    #[DataProvider('validValueProvider')]
    public function her_tip_icin_gecerli_deger_kaydedilir(string $type, mixed $value): void
    {
        $definition = CustomFieldDefinition::create([
            'key' => 'alan-'.uniqid(),
            'label' => 'Alan',
            'type' => $type,
            'options' => in_array($type, ['select', 'multi_select'], true) ? ['A', 'B'] : null,
        ]);

        $customer = Customer::create(['name' => 'Firma']);
        app(CustomFieldService::class)->syncCustomerValues($customer, [$definition->id => $value]);

        $stored = CustomFieldValue::query()
            ->where('definition_id', $definition->id)
            ->where('customer_id', $customer->id)
            ->first();

        $this->assertNotNull($stored);
        $this->assertSame($value, $stored->typedValue());
    }

    public static function invalidValueProvider(): array
    {
        return [
            'integer-olsun-ama-string' => ['integer', 'otuz'],
            'select-disinda' => ['select', 'C'],
            'multi-select-disinda' => ['multi_select', ['C']],
            'decimal-olsun-ama-string' => ['decimal', 'abc'],
        ];
    }

    #[Test]
    #[DataProvider('invalidValueProvider')]
    public function gecersiz_deger_reddedilir(string $type, mixed $value): void
    {
        $definition = CustomFieldDefinition::create([
            'key' => 'gecerli-alan-'.uniqid(),
            'label' => 'Alan',
            'type' => $type,
            'options' => ['A', 'B'],
        ]);

        $customer = Customer::create(['name' => 'Firma']);

        $this->expectException(ValidationException::class);
        app(CustomFieldService::class)->syncCustomerValues($customer, [$definition->id => $value]);
    }

    #[Test]
    public function zorunlu_alan_bos_birakilamazin(): void
    {
        $definition = CustomFieldDefinition::create([
            'key' => 'zorunlu-alan',
            'label' => 'Zorunlu',
            'type' => 'text',
            'required' => true,
        ]);

        $customer = Customer::create(['name' => 'Firma']);

        $this->expectException(ValidationException::class);
        app(CustomFieldService::class)->syncCustomerValues($customer, [$definition->id => '']);
    }

    #[Test]
    public function tip_degisikligi_mevcut_deger_varsa_reddedilir(): void
    {
        $definition = CustomFieldDefinition::create([
            'key' => 'degisen-alan',
            'label' => 'Değişen',
            'type' => 'text',
        ]);

        $customer = Customer::create(['name' => 'Firma']);
        app(CustomFieldService::class)->syncCustomerValues($customer, [$definition->id => 'eski değer']);

        $this->expectException(ValidationException::class);
        app(CustomFieldService::class)->upsertDefinition([
            'key' => 'degisen-alan',
            'label' => 'Değişen',
            'type' => 'integer',
        ], $definition);
    }

    #[Test]
    public function uyumlu_tip_degisikligi_serbest(): void
    {
        $definition = CustomFieldDefinition::create([
            'key' => 'metin-alan',
            'label' => 'Metin',
            'type' => 'text',
        ]);

        $customer = Customer::create(['name' => 'Firma']);
        app(CustomFieldService::class)->syncCustomerValues($customer, [$definition->id => 'değer']);

        $updated = app(CustomFieldService::class)->upsertDefinition([
            'key' => 'metin-alan',
            'label' => 'Metin',
            'type' => 'textarea',
        ], $definition);

        $this->assertSame('textarea', $updated->type);
        // Değer korunur
        $this->assertSame('değer', $definition->values()->first()?->typedValue());
    }

    #[Test]
    public function deactivate_sonrasi_degerler_korunur(): void
    {
        $definition = CustomFieldDefinition::create([
            'key' => 'pasif-olacak',
            'label' => 'Pasif',
            'type' => 'text',
        ]);

        $customer = Customer::create(['name' => 'Firma']);
        app(CustomFieldService::class)->syncCustomerValues($customer, [$definition->id => 'değer']);

        $this->patch('/app/settings/custom-fields/'.$definition->id.'/deactivate')->assertRedirect();

        $this->assertFalse($definition->fresh()->is_active);
        $this->assertTrue(CustomFieldValue::query()->where('definition_id', $definition->id)->exists());
        $this->assertTrue(
            AuditEvent::query()->where('action', 'deactivated')->where('auditable_id', $definition->id)->exists()
        );
    }
}
