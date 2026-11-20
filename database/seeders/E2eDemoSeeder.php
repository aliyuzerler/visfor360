<?php

namespace Database\Seeders;

use App\Domain\Checkups\Models\CheckupQuestion;
use App\Domain\Checkups\Models\CheckupRule;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\DocumentType;
use App\Domain\Partners\Models\Partner;
use App\Domain\Partners\Models\PartnerAgreement;
use App\Domain\Partners\Models\PartnerWork;
use App\Domain\Portal\Models\PortalDocumentRequest;
use App\Domain\Portal\Models\PortalMembership;
use App\Domain\Portal\Models\PortalUser;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ServiceType;
use App\Domain\Tasks\Models\Task;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * E2E demo verisi (yalnız geliştirme/test ortamı): Playwright akışlarının
 * gerçekçi veriyle çalışması için. Üretimde çalıştırılmaz.
 */
class E2eDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Yönetici
        $admin = User::firstOrCreate(
            ['email' => 'admin@visfor.example'],
            ['name' => 'Demo Yönetici', 'password' => 'Password123456', 'is_active' => true],
        );
        $sysAdminRole = Role::query()->where('key', 'sistem-yoneticisi')->first();
        if ($sysAdminRole !== null) {
            $admin->roles()->syncWithoutDetaching([$sysAdminRole->id]);
        }

        // Müşteriler
        $custA = Customer::firstOrCreate(
            ['name' => 'E2E Portal Firması'],
            ['city' => 'Ankara', 'email' => 'info@e2eporta.example'],
        );
        Customer::firstOrCreate(['name' => 'Anadolu Turizm A.Ş.'], ['city' => 'İstanbul']);

        // Pipeline + hizmet türü
        $pipeline = Pipeline::firstOrCreate(['key' => 'e2e-pipe'], ['label' => 'Danışmanlık Şablonu']);
        $stageKeşif = PipelineStage::firstOrCreate(
            ['pipeline_id' => $pipeline->id, 'key' => 'asama-1'],
            ['label' => 'Keşif', 'sort_order' => 1],
        );
        PipelineStage::firstOrCreate(
            ['pipeline_id' => $pipeline->id, 'key' => 'asama-2'],
            ['label' => 'Yürütme', 'sort_order' => 2],
        );
        $department = Department::firstOrCreate(['name' => 'Danışmanlık Ekibi'], ['code' => 'danismanlik-e2e']);
        $service = ServiceType::firstOrCreate(
            ['key' => 'sv_danismanlik'],
            ['label' => 'Danışmanlık Hizmeti', 'pipeline_id' => $pipeline->id, 'department_id' => $department->id],
        );

        // Proje + görev
        $project = Project::firstOrCreate(
            ['name' => 'E2E Dijital Dönüşüm'],
            ['customer_id' => $custA->id, 'service_type_id' => $service->id, 'pipeline_stage_id' => $stageKeşif->id],
        );
        Task::firstOrCreate(
            ['title' => 'E2E Kapsam Raporu'],
            ['customer_id' => $custA->id, 'project_id' => $project->id, 'due_date' => now()->addDays(3)->toDateString()],
        );

        // Check-Up şablonu
        $template = CheckupTemplate::firstOrCreate(
            ['key' => 'dijital-olgunluk-ornek'],
            ['title' => 'Dijital Olgunluk Değerlendirmesi', 'status' => 'published'],
        );
        CheckupQuestion::firstOrCreate(
            ['template_id' => $template->id, 'key' => 'cihaz_sayisi'],
            ['label' => 'Yönetilen cihaz sayısı', 'type' => 'number', 'required' => true, 'sort_order' => 1],
        );
        CheckupQuestion::firstOrCreate(
            ['template_id' => $template->id, 'key' => 'sektor'],
            ['label' => 'Sektörünüz', 'type' => 'single_choice', 'required' => true, 'sort_order' => 2, 'config' => ['options' => ['üretim', 'turizm', 'perakende']]],
        );
        CheckupRule::firstOrCreate(
            ['template_id' => $template->id, 'label' => 'Altyapı Değerlendirmesi'],
            [
                'priority' => 10,
                'condition_groups' => [['conditions' => [['question_key' => 'cihaz_sayisi', 'operator' => 'gte', 'value' => 50]]]],
                'recommendation_service_key' => 'sv_altyapi',
                'recommendation_label' => 'Altyapı Değerlendirmesi',
            ],
        );
        CheckupRule::firstOrCreate(
            ['template_id' => $template->id, 'label' => 'Turizm Dijital Paketi'],
            [
                'priority' => 20,
                'condition_groups' => [['conditions' => [['question_key' => 'sektor', 'operator' => 'eq', 'value' => 'turizm']]]],
                'recommendation_service_key' => 'sv_turizm',
                'recommendation_label' => 'Turizm Dijital Paketi',
            ],
        );

        // Ortak + anlaşma + iş
        $partner = Partner::firstOrCreate(
            ['name' => 'Mimarlık Bürosu'],
            ['expertise_type' => 'mimarluk', 'is_active' => true],
        );
        PartnerAgreement::firstOrCreate(
            ['partner_id' => $partner->id, 'commission_type' => 'percent_of_value'],
            ['commission_rate' => '10', 'currency_code' => 'TRY', 'status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now()],
        );
        PartnerWork::firstOrCreate(
            ['partner_id' => $partner->id, 'customer_id' => $custA->id, 'title' => 'Yönlendirilen danışmanlık'],
            ['referral_type' => 'referral', 'value' => '50000', 'currency_code' => 'TRY'],
        );

        // Portal üyesi + belge talebi
        $portalUser = PortalUser::firstOrCreate(
            ['email' => 'e2e@firmaya.example'],
            ['name' => 'E2E Portal Kullanıcısı', 'password' => 'Password123456'],
        );
        PortalMembership::firstOrCreate(['portal_user_id' => $portalUser->id, 'customer_id' => $custA->id]);
        $docType = DocumentType::firstOrCreate(
            ['key' => 'e2e-doc'],
            ['label' => 'Firma Vergi Levhası', 'required' => false],
        );
        PortalDocumentRequest::firstOrCreate(
            ['customer_id' => $custA->id, 'document_type_id' => $docType->id],
            ['status' => 'requested'],
        );

        $this->command?->info('E2E demo verisi hazır.');
    }
}
