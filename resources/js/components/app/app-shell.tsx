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

function NavItems({ currentRoute }: { currentRoute: string }) {
    return (
        <nav className="space-y-6" aria-label="Ana menü">
            {NAV_GROUPS.map((group) => (
                <div key={group.key}>
                    <p className="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {group.label}
                    </p>
                    <ul className="space-y-0.5">
                        {group.items.map((item) => (
                            <li key={item.key}>
                                <NavLink
                                    item={item}
                                    active={route(item.route, undefined, false) === currentRoute}
                                />
                            </li>
                        ))}
                    </ul>
                </div>
            ))}
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
                    ? 'bg-primary text-primary-foreground'
                    : 'text-foreground hover:bg-surface-muted',
            )}
            aria-current={active ? 'page' : undefined}
        >
            <Icon className="h-4 w-4 shrink-0" aria-hidden />
            <span className="flex-1 truncate">{item.label}</span>
            {item.phase === '2' && (
                <Badge variant="info" className="shrink-0">
                    Faz 2
                </Badge>
            )}
        </Link>
    );
}

function SidebarContent() {
    const currentRoute = usePage().url;
    return (
        <>
            <div className="flex items-center gap-2.5 px-3 py-4">
                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-foreground">
                    V
                </div>
                <div>
                    <p className="text-sm font-semibold text-foreground">Visfor360</p>
                    <p className="text-xs text-muted-foreground">Bütünleşik yönetim</p>
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
                    <header className="sticky top-0 z-40 flex h-14 items-center justify-between gap-3 border-b border-border bg-surface/95 px-4 pl-14 backdrop-blur lg:pl-4">
                        <p className="truncate text-sm font-semibold text-foreground lg:hidden">
                            Visfor360
                        </p>
                        <div className="flex-1" />
                        <UserMenu user={auth.user} />
                    </header>
                    <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6">
                        <h1 className="sr-only">{title}</h1>
                        {children}
                    </main>
                </div>
            </div>
        </TooltipProvider>
    );
}
