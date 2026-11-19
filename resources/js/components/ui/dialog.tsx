import * as DialogPrimitive from '@radix-ui/react-dialog';
import { X } from 'lucide-react';
import * as React from 'react';
import { cn } from '@/lib/utils';

/** Dialog (modal): odak yakalama, ESC kapatma ve odak dönüşü Radix ile sağlanır. */
export const Dialog = DialogPrimitive.Root;
export const DialogTrigger = DialogPrimitive.Trigger;
export const DialogClose = DialogPrimitive.Close;

export function DialogContent({
    className,
    title,
    description,
    children,
    ...props
}: React.ComponentPropsWithoutRef<typeof DialogPrimitive.Content> & {
    title: string;
    description?: string;
}) {
    return (
        <DialogPrimitive.Portal>
            <DialogPrimitive.Overlay className="fixed inset-0 z-[100] bg-slate-950/40 transition-opacity" />
            <DialogPrimitive.Content
                className={cn(
                    'fixed left-1/2 top-1/2 z-[100] w-[calc(100%-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl border border-border bg-surface p-6 shadow-[var(--shadow-popover)]',
                    className,
                )}
                {...props}
            >
                <div className="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <DialogPrimitive.Title className="text-base font-semibold text-foreground">
                            {title}
                        </DialogPrimitive.Title>
                        {description && (
                            <DialogPrimitive.Description className="mt-1 text-sm text-muted-foreground">
                                {description}
                            </DialogPrimitive.Description>
                        )}
                    </div>
                    <DialogPrimitive.Close
                        className="rounded-md p-1 text-muted-foreground hover:bg-surface-muted"
                        aria-label="Kapat"
                    >
                        <X className="h-4 w-4" aria-hidden />
                    </DialogPrimitive.Close>
                </div>
                {children}
            </DialogPrimitive.Content>
        </DialogPrimitive.Portal>
    );
}
