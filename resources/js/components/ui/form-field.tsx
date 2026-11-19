import * as React from 'react';
import { cn } from '@/lib/utils';

export interface FormFieldProps {
    /** Kontrolün id'si; label bununla html_for üzerinden eşlenir. */
    name: string;
    label: string;
    hint?: string;
    /**Alan düzeyi doğrulama mesajı; varsa aria-invalid ve aria-describedby set edilir. */
    error?: string;
    required?: boolean;
    children: (props: {
        id: string;
        'aria-describedby'?: string;
        'aria-invalid'?: true;
        'aria-required'?: true;
    }) => React.ReactNode;
}

/**
 * Etiket/ipucu/hata ilişkilendirmesini tek yerde yöneten form alanı bileşeni.
 */
export function FormField({ name, label, hint, error, required, children }: FormFieldProps) {
    const id = `${name}-field`;
    const hintId = `${name}-hint`;
    const errorId = `${name}-error`;
    const describedBy = [hint ? hintId : null, error ? errorId : null].filter(Boolean).join(' ');

    return (
        <div className="space-y-1.5">
            <label htmlFor={id} className="block text-sm font-medium text-foreground">
                {label}
                {required && (
                    <span className="ml-1 text-danger" aria-hidden>
                        *
                    </span>
                )}
            </label>
            {children({
                id,
                ...(describedBy ? { 'aria-describedby': describedBy } : {}),
                ...(error ? { 'aria-invalid': true as const } : {}),
                ...(required ? { 'aria-required': true as const } : {}),
            })}
            {hint && (
                <p id={hintId} className="text-xs text-muted-foreground">
                    {hint}
                </p>
            )}
            {error && (
                <p id={errorId} className="text-xs font-medium text-danger" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}

export function Checkbox({
    className,
    label,
    ...props
}: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
    return (
        <label className={cn('inline-flex items-center gap-2 text-sm text-foreground', className)}>
            <input
                type="checkbox"
                className="h-4 w-4 rounded border-border accent-[var(--color-primary)]"
                {...props}
            />
            {label}
        </label>
    );
}
