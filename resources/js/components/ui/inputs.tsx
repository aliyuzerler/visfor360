import * as React from 'react';
import { cn } from '@/lib/utils';

const base =
    'flex w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50';

export function Input({ className, ...props }: React.InputHTMLAttributes<HTMLInputElement>) {
    return <input className={cn(base, 'h-9', className)} {...props} />;
}

export function Textarea({
    className,
    ...props
}: React.TextareaHTMLAttributes<HTMLTextAreaElement>) {
    return <textarea className={cn(base, 'min-h-20', className)} {...props} />;
}

export function Select({
    className,
    children,
    ...props
}: React.SelectHTMLAttributes<HTMLSelectElement>) {
    return (
        <select className={cn(base, 'h-9', className)} {...props}>
            {children}
        </select>
    );
}
