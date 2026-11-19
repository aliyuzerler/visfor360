import { Toaster as SonnerToaster, toast } from 'sonner';

/**
 * Bildirimler (toast): async işlemlerin sonucunu aria-live ile duyurur.
 * Kullanım: import { toast } from '@/components/ui/toast';
 */
export function Toaster() {
    return (
        <SonnerToaster
            position="top-right"
            toastOptions={{
                classNames: {
                    toast:
                        '!bg-surface !border !border-border !text-foreground !shadow-[var(--shadow-popover)] !rounded-lg !text-sm',
                    description: '!text-muted-foreground',
                },
            }}
        />
    );
}

export { toast };
