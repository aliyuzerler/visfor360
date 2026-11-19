import * as TabsPrimitive from '@radix-ui/react-tabs';
import * as TooltipPrimitive from '@radix-ui/react-tooltip';
import * as React from 'react';
import { cn } from '@/lib/utils';

/** Sekmeler: klavye ile ok tuşlarıyla gezilebilir (Radix). */
export const Tabs = TabsPrimitive.Root;

export function TabsList({ className, ...props }: React.ComponentPropsWithoutRef<typeof TabsPrimitive.List>) {
    return (
        <TabsPrimitive.List
            className={cn('flex gap-1 border-b border-border', className)}
            {...props}
        />
    );
}

export function TabsTrigger({
    className,
    ...props
}: React.ComponentPropsWithoutRef<typeof TabsPrimitive.Trigger>) {
    return (
        <TabsPrimitive.Trigger
            className={cn(
                '-mb-px border-b-2 border-transparent px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground',
                className,
            )}
            {...props}
        />
    );
}

export function TabsContent({
    className,
    ...props
}: React.ComponentPropsWithoutRef<typeof TabsPrimitive.Content>) {
    return (
        <TabsPrimitive.Content className={cn('pt-4 outline-none', className)} {...props} />
    );
}

/** Tooltip: hover ve focus'ta görünür; klavye kullanıcıları için de erişilebilir. */
export const TooltipProvider = TooltipPrimitive.Provider;

export function Tooltip({
    content,
    children,
}: {
    content: string;
    children: React.ReactNode;
}) {
    return (
        <TooltipPrimitive.Root delayDuration={300}>
            <TooltipPrimitive.Trigger asChild>{children}</TooltipPrimitive.Trigger>
            <TooltipPrimitive.Portal>
                <TooltipPrimitive.Content
                    className="z-[100] rounded-md bg-foreground px-2 py-1 text-xs text-primary-foreground shadow-md"
                    sideOffset={4}
                >
                    {content}
                </TooltipPrimitive.Content>
            </TooltipPrimitive.Portal>
        </TooltipPrimitive.Root>
    );
}
