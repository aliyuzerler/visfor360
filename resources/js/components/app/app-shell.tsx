import * as DialogPrimitive from '@radix-ui/react-dialog';
import {
    BarChart3,
    Building2,
    ClipboardCheck,
    FolderKanban,
    Handshake,
    LayoutDashboard,
    Lightbulb,
    ListChecks,
    Menu,
    Settings,
    Target,
    Wallet,
    type LucideIcon,
} from 'lucide-react';
import type { PageProps } from '@/types';
import { NAV_GROUPS, type NavItem } from '@/nav';
import { Link, usePage } from '@inertiajs/react';
import { route } from '@/lib/route';
import * as React from 'react';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/indicators';
import { TooltipProvider } from '@/components/ui/tabs-tooltip';
import { UserMenu } from '@/components/app/user-menu';
import { Toaster } from '@/components/ui/toast';

const ICONS: Record<string, LucideIcon> = {
    LayoutDashboard,
    Building2,
    FolderKanban,
    ListChecks,
    Target,
    Wallet,
    ClipboardCheck,
    Lightbulb,
    Handshake,
    BarChart3,
    Settings,
};

/** Ayarlar ekranı için izin eşlemesi (UI görünürlüğü; gerçek kontrol server-side). */
const ROUTE_PERMISSION: Record<string, string> = {
    'settings.users.index': 'users.view',
    'settings.roles.index': 'roles.view',
    'settings.departments.index': 'departments.view',
    'settings.audit-logs.index': 'audit.view',
};

function NavItems({ currentRoute }: { currentRoute: string }) {
    const { auth } = usePage<PageProps>().props;
    const permissions = auth.user?.permissions ?? [];
    const can = (routeName: string): boolean => {
        const needed = ROUTE_PERMISSION[routeName];
        if (routeName === 'settings.index') {
            // Hub: en az bir ayar ekranına erişim varsa görünür
            return Object.values(ROUTE_PERMISSION).some((perm) => permissions.includes(perm));
        }
        if (!needed) return true;
        return permissions.includes(needed) || permissions.includes('*');
    };

    return (
        <nav className="space-y-6" aria-label="Ana menü">
            {NAV_GROUPS.map((group) => {
                const visible = group.items.filter((item) => can(item.route));
                if (visible.length === 0) return null;
                return (
                    <div key={group.key}>
                        <p className="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {group.label}
                        </p>
                        <ul className="space-y-0.5">
                            {visible.map((item) => (
                                <li key={item.key}>
                                    <NavLink
                                        item={item}
                                        active={route(item.route, undefined, false) === currentRoute}
                                    />
                                </li>
                            ))}
                        </ul>
                    </div>
                );
            })}
        </nav>
    );
}

function NavLink({ item, active }: { item: NavItem; active: boolean }) {
    const Icon = ICONS[item.icon] ?? LayoutDashboard;
    return (
        <Link
            href={route(item.route, undefined, false)}
            className={cn(
                'flex items-center gap-2.5 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                active
                    ? 'relative bg-primary text-primary-foreground shadow-sm'
                    : 'text-foreground hover:bg-surface-muted',
            )}
            aria-current={active ? 'page' : undefined}
        >
            <Icon className="h-4 w-4 shrink-0" aria-hidden />
            <span className="flex-1 truncate">{item.label}</span>
        </Link>
    );
}

function SidebarContent() {
    const currentRoute = usePage().url;
    return (
        <>
            <div className="flex items-center gap-2.5 border-b border-border px-4 py-4">
                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/70 text-sm font-bold text-primary-foreground shadow-md">
                    V
                </div>
                <div>
                    <p className="text-sm font-semibold tracking-tight text-foreground">Visfor360</p>
                    <p className="text-[11px] text-muted-foreground">Bütünleşik yönetim platformu</p>
                </div>
            </div>
            <div className="flex-1 overflow-y-auto px-3 pb-4">
                <NavItems currentRoute={currentRoute} />
            </div>
        </>
    );
}

/**
 * Uygulama kabuğu: masaüstünde sabit sidebar, mobilde Radix Dialog ile açılır menü.
 * Ekranlar docs/SCREEN_MAP.md ile eşleşir.
 */
export function AppShell({ title, children }: { title: string; children: React.ReactNode }) {
    const { auth } = usePage<PageProps>().props;

    return (
        <TooltipProvider>
            <Toaster />
            <div className="flex min-h-screen bg-background">
                {/* Masaüstü sidebar */}
                <aside className="sticky top-0 hidden h-screen w-64 shrink-0 flex-col border-r border-border bg-surface lg:flex">
                    <SidebarContent />
                </aside>

                {/* Mobil menü (dialog tabanlı, odak yönetimi Radix ile) */}
                <DialogPrimitive.Root>
                    <DialogPrimitive.Trigger
                        className="fixed left-3 top-3 z-50 rounded-md border border-border bg-surface p-2 text-foreground shadow-sm lg:hidden"
                        aria-label="Menüyü aç"
                    >
                        <Menu className="h-5 w-5" aria-hidden />
                    </DialogPrimitive.Trigger>
                    <DialogPrimitive.Portal>
                        <DialogPrimitive.Overlay className="fixed inset-0 z-[100] bg-slate-950/40" />
                        <DialogPrimitive.Content className="fixed left-0 top-0 z-[100] flex h-full w-72 max-w-[85%] flex-col border-r border-border bg-surface">
                            <DialogPrimitive.Title className="sr-only">
                                Navigasyon menüsü
                            </DialogPrimitive.Title>
                            <SidebarContent />
                        </DialogPrimitive.Content>
                    </DialogPrimitive.Portal>
                </DialogPrimitive.Root>

                {/* İçerik bölgesi */}
                <div className="flex min-w-0 flex-1 flex-col">
                    <header className="sticky top-0 z-40 flex h-16 items-center justify-between gap-3 border-b border-border bg-surface/95 px-4 pl-14 shadow-sm backdrop-blur lg:pl-6">
                        <div className="flex min-w-0 flex-1 items-center gap-3">
                            <p className="hidden text-sm font-semibold text-foreground sm:block">{title}</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <Link
                                href={route('dashboard')}
                                className="hidden items-center gap-1.5 rounded-md border border-border px-2.5 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-surface-muted hover:text-foreground sm:flex"
                            >
                                <BarChart3 className="h-3.5 w-3.5" aria-hidden />
                                Genel Bakış
                            </Link>
                            <UserMenu user={auth.user} />
                        </div>
                    </header>
                    <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6">
                        <h1 className="sr-only">{title}</h1>
                        {children}
                    </main>
                    <footer className="border-t border-border bg-surface px-4 py-4 sm:px-6">
                        <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
                            <p>© {new Date().getFullYear()} Visfor360 · Bütünleşik yönetim platformu</p>
                            <p className="flex items-center gap-3">
                                <span>v1.0.0</span>
                                <Link href={route('dashboard')} className="hover:text-foreground">Ana sayfa</Link>
                            </p>
                        </div>
                    </footer>
                </div>
            </div>
        </TooltipProvider>
    );
}
