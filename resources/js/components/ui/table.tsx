import * as React from 'react';
import { cn } from '@/lib/utils';

/**
 * Sunucu tarafı sayfalama/filtreleme ile kullanılan tablo primitifleri.
 * Tablolar her zaman <thead><th scope="col"> yapısıyla yazılır (a11y).
 */
export function Table({ className, ...props }: React.TableHTMLAttributes<HTMLTableElement>) {
    return (
        <div className="w-full overflow-x-auto">
            <table className={cn('w-full border-collapse text-sm', className)} {...props} />
        </div>
    );
}

export function THead({ className, ...props }: React.HTMLAttributes<HTMLTableSectionElement>) {
    return <thead className={cn('bg-surface-muted text-left', className)} {...props} />;
}

export function TH({ className, ...props }: React.ThHTMLAttributes<HTMLTableCellElement>) {
    return (
        <th
            className={cn('border-b border-border px-4 py-2.5 font-medium text-muted-foreground', className)}
            {...props}
        />
    );
}

export function TBody({ className, ...props }: React.HTMLAttributes<HTMLTableSectionElement>) {
    return <tbody className={cn('divide-y divide-border', className)} {...props} />;
}

export function TR({ className, ...props }: React.HTMLAttributes<HTMLTableRowElement>) {
    return <tr className={cn('hover:bg-surface-muted/60', className)} {...props} />;
}

export function TD({ className, ...props }: React.TdHTMLAttributes<HTMLTableCellElement>) {
    return <td className={cn('px-4 py-2.5 text-foreground', className)} {...props} />;
}
