import { Badge } from '@/components/ui/indicators';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head } from '@inertiajs/react';

interface TemplateRow {
    id: number;
    key: string;
    title: string;
    version: number;
    status: string;
    is_example: boolean;
    question_count: number;
    rule_count: number;
}

/** Sablon yoneticisi (prototip): musteri soru seti gelmeden yalniz ornek sablonlar. */
export default function CheckupSettings({ templates, blocked }: { templates: TemplateRow[]; blocked: boolean }) {
    return (
        <>
            <Head title="Check-Up Şablonları" />
            <AppShell title="Check-Up Şablonları">
                <PageHeader
                    title="Check-Up Şablonları ve Kural Motoru"
                    description="Versiyonlu şablonlar ve deklaratif kural seti. Kural motoru AND/OR koşul grupları, izinli operator listesi ve öncelikle deterministik çalışır (eval/dynamic execution yok)."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Check-Up Şablonları' },
                    ]}
                />

                {blocked && (
                    <div className="mb-4 rounded-xl border border-warning/40 bg-warning/10 p-4 text-sm" role="alert">
                        <strong className="font-semibold">Blokaj:</strong> Müşteri Check-Up soru seti, cevap türleri ve cevap-hizmet
                        karar tablosu henüz teslim edilmedi. Üretim hizmet öneri kuralı yazılmıyor; yalnız ÖRNEK etiketli prototip
                        şablon ve motor fixture'ları mevcut (ADR-0010).
                    </div>
                )}

                <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                    {templates.map((template) => (
                        <li key={template.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                            <div>
                                <p className="font-medium">
                                    {template.title} <span className="text-xs text-muted-foreground">v{template.version}</span>
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {template.question_count} soru · {template.rule_count} kural · {template.status}
                                </p>
                            </div>
                            {template.is_example && <Badge variant="warning">ÖRNEK</Badge>}
                        </li>
                    ))}
                </ul>
            </AppShell>
        </>
    );
}
