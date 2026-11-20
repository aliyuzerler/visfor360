import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';

/** Sade portal layout: iç uygulama layout'u ile hiçbir ortak bileşen yok. */
export function PortalLayout({ children, title }: { children: ReactNode; title: string }) {
    return (
        <div className="min-h-screen bg-background">
            <header className="border-b border-border bg-surface">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                    <Link href="/portal" className="text-base font-semibold text-foreground">
                        Visfor360 Müşteri Portalı
                    </Link>
                    <a href="/portal/logout" className="text-sm text-muted-foreground hover:text-foreground">
                        Çıkış
                    </a>
                </div>
            </header>
            <main className="mx-auto max-w-5xl px-4 py-6">
                <h1 className="sr-only">{title}</h1>
                {children}
            </main>
            <footer className="mx-auto max-w-5xl px-4 py-6 text-xs text-muted-foreground">
                Bu portal yalnız kendi firmanıza ait bilgileri gösterir.
            </footer>
        </div>
    );
}
