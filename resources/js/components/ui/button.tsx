import { cva, type VariantProps } from 'class-variance-authority';
import { LoaderCircle } from 'lucide-react';
import * as React from 'react';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors duration-[var(--duration-fast)] disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                primary: 'bg-primary text-primary-foreground hover:bg-primary-hover',
                secondary:
                    'border border-border bg-surface text-foreground hover:bg-surface-muted',
                tertiary: 'text-primary hover:bg-surface-muted',
                destructive: 'bg-danger text-white hover:opacity-90',
            },
            size: {
                sm: 'h-8 px-3',
                md: 'h-9 px-4',
                lg: 'h-10 px-5',
                icon: 'h-9 w-9',
            },
        },
        defaultVariants: {
            variant: 'primary',
            size: 'md',
        },
    },
);

export interface ButtonProps
    extends React.ButtonHTMLAttributes<HTMLButtonElement>,
        VariantProps<typeof buttonVariants> {
    /** Yükleme durumunda spinner gösterir ve butonu devre dışı bırakır. */
    loading?: boolean;
}

export function Button({
    className,
    variant,
    size,
    loading = false,
    disabled,
    children,
    ...props
}: ButtonProps) {
    return (
        <button
            className={cn(buttonVariants({ variant, size }), className)}
            disabled={disabled || loading}
            aria-busy={loading || undefined}
            {...props}
        >
            {loading && <LoaderCircle className="h-4 w-4 animate-spin" aria-hidden />}
            {children}
        </button>
    );
}

export { buttonVariants };
