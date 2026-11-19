import * as React from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

/** Breadcrumb: son öğe mevcut sayfa (aria-current), tıklanabilir öğeler link. */
export function Breadcrumbs({
    items,
    className,
}: {
    items: BreadcrumbItem[];
    className?: string;
}) {
    return (
        <nav aria-label="Sayfa yolu" className={cn('text-sm text-muted-foreground', className)}>
            <ol className="flex flex-wrap items-center gap-1">
                {items.map((item, index) => {
                    const isLast = index === items.length - 1;
                    return (
                        <li key={item.label} className="flex items-center gap-1">
                            {isLast ? (
                                <span aria-current="page" className="font-medium text-foreground">
                                    {item.label}
                                </span>
                            ) : (
                                <>
                                    {item.href ? (
                                        <Link href={item.href} className="hover:text-foreground">
                                            {item.label}
                                        </Link>
                                    ) : (
                                        <span>{item.label}</span>
                                    )}
                                    <ChevronRight className="h-3.5 w-3.5" aria-hidden />
                                </>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}

/** Sayfa başlığı: her ekranın ana görevi + primary action'ı burada belirginleşir. */
export function PageHeader({
    title,
    description,
    actions,
    breadcrumbs,
    className,
}: {
    title: string;
    description?: string;
    actions?: React.ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    className?: string;
}) {
    return (
        <header className={cn('mb-6 space-y-2', className)}>
            {breadcrumbs && <Breadcrumbs items={breadcrumbs} />}
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-foreground">{title}</h1>
                    {description && <p className="mt-1 text-sm text-muted-foreground">{description}</p>}
                </div>
                {actions && <div className="flex items-center gap-2">{actions}</div>}
            </div>
        </header>
    );
}

/**
 * Empty state: ne boş olduğunu açıklar; izin varsa ilk aksiyona yönlendirir.
 * Sahte veri göstermek yerine bunu kullanın.
 */
export function EmptyState({
    title,
    description,
    action,
    icon,
}: {
    title: string;
    description?: string;
    action?: React.ReactNode;
    icon?: React.ReactNode;
}) {
    return (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border bg-surface px-6 py-12 text-center">
            {icon && <div className="mb-3 text-muted-foreground [&>svg]:h-8 [&>svg]:w-8">{icon}</div>}
            <p className="text-sm font-medium text-foreground">{title}</p>
            {description && <p className="mt-1 max-w-sm text-sm text-muted-foreground">{description}</p>}
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}
