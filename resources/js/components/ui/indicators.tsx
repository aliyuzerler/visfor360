import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { cn } from '@/lib/utils';

/* Badge — durumlar yalnız renkle değil, metinle de anlatılır (a11y). */
const badgeVariants = cva(
    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
    {
        variants: {
            variant: {
                neutral: 'bg-surface-muted text-muted-foreground border border-border',
                info: 'bg-info-bg text-info',
                success: 'bg-success-bg text-success',
                warning: 'bg-warning-bg text-warning',
                danger: 'bg-danger-bg text-danger',
            },
        },
        defaultVariants: { variant: 'neutral' },
    },
);

export function Badge({
    className,
    variant,
    children,
    ...props
}: React.HTMLAttributes<HTMLSpanElement> & VariantProps<typeof badgeVariants>) {
    return (
        <span className={cn(badgeVariants({ variant }), className)} {...props}>
            {children}
        </span>
    );
}

export function Card({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn(
                'rounded-xl border border-border bg-surface shadow-[var(--shadow-card)]',
                className,
            )}
            {...props}
        />
    );
}

export function CardHeader({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('border-b border-border px-5 py-4', className)} {...props} />;
}

export function CardTitle({ className, ...props }: React.HTMLAttributes<HTMLHeadingElement>) {
    return <h2 className={cn('text-sm font-semibold text-foreground', className)} {...props} />;
}

export function CardBody({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('px-5 py-4', className)} {...props} />;
}

export function Skeleton({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn('animate-pulse rounded-md bg-surface-muted', className)}
            aria-hidden
            {...props}
        />
    );
}
