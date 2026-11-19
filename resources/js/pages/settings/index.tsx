import { Badge } from '@/components/ui/indicators';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link } from '@inertiajs/react';

interface Section {
    key: string;
    title: string;
    description: string;
    route: string;
    can: boolean;
}

/** Ayarlar hub'ı: erişilebilir bölümler gerçek sayfalara bağlanır. */
export default function SettingsIndex({
    sections,
    stats,
}: { sections: Section[]; stats: { users: number; departments: number } }) {
    return (
        <>
            <Head title="Ayarlar" />
            <AppShell title="Ayarlar">
                <PageHeader
                    title="Ayarlar"
                    description="Kullanıcı, rol, departman yönetimi ve audit kayıtları."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Ayarlar' }]}
                />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {sections.map((section) => (
                        <Link
                            key={section.key}
                            href={route(section.route)}
                            className="block rounded-xl border border-border bg-surface p-5 shadow-[var(--shadow-card)] transition-colors hover:bg-surface-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--color-primary)]"
                        >
                            <h2 className="text-sm font-semibold text-foreground">{section.title}</h2>
                            <p className="mt-1 text-sm text-muted-foreground">{section.description}</p>
                            {section.key === 'users' && (
                                <Badge className="mt-3">{stats.users} kullanıcı</Badge>
                            )}
                            {section.key === 'departments' && (
                                <Badge className="mt-3">{stats.departments} aktif departman</Badge>
                            )}
                        </Link>
                    ))}
                </div>
                {sections.length === 0 && (
                    <p className="rounded-xl border border-dashed border-border bg-surface p-6 text-sm text-muted-foreground">
                        Ayarlar bölümüne erişim izniniz yok. Sistem yöneticinizle iletişime geçin.
                    </p>
                )}
            </AppShell>
        </>
    );
}
