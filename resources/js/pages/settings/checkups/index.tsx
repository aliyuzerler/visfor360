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
                    description="Versiyonlu Check-Up şablonları ve deklaratif kural seti; motor koşul grupları ve öncelik sırasıyla deterministik çalışır."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Check-Up Şablonları' },
                    ]}
                />

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
                        </li>
                    ))}
                </ul>
            </AppShell>
        </>
    );
}
