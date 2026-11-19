import { Badge } from '@/components/ui/indicators';
import { EmptyState } from '@/components/ui/page';
import { AppShell } from '@/components/app/app-shell';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import * as React from 'react';

/**
 * Faz kapsamı tamamlanmamış ekranlar için dürüst wireframe sayfası.
 * Sahte KPI/çalışmayan buton yerine plan bilgisi ve boş durum gösterilir.
 */
export default function PlaceholderPage({
    title,
    description,
    subScreens,
    phase,
}: {
    title: string;
    description: string;
    subScreens: string[];
    phase: '1' | '2';
}) {
    return (
        <>
            <Head title={title} />
            <AppShell title={title}>
                <div className="space-y-4">
                    <div className="flex flex-wrap items-center gap-2">
                        <h2 className="text-lg font-semibold text-foreground">{title}</h2>
                        <Badge variant={phase === '2' ? 'info' : 'neutral'}>
                            {phase === '2' ? 'Faz 2 kapsamı' : 'Faz 1 kapsamı'}
                        </Badge>
                    </div>
                    <p className="max-w-3xl text-sm text-muted-foreground">{description}</p>
                    <div className="rounded-xl border border-border bg-surface p-5">
                        <p className="text-sm font-medium text-foreground">Planlanan ekranlar</p>
                        <ul className="mt-2 list-disc space-y-1 pl-5 text-sm text-muted-foreground">
                            {subScreens.map((screen) => (
                                <li key={screen}>{screen}</li>
                            ))}
                        </ul>
                    </div>
                    <EmptyState
                        title="Bu ekran henüz geliştirilmedi"
                        description="İlgili modülün geliştirme fazı tamamlandığında bu sayfa gerçek veri ve işlevleriyle sunulacak. Sahte veri gösterilmez."
                    />
                </div>
            </AppShell>
        </>
    );
}
