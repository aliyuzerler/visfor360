import * as DropdownMenuPrimitive from '@radix-ui/react-dropdown-menu';
import { ChevronDown, LogOut } from 'lucide-react';
import { useForm } from '@inertiajs/react';
import * as React from 'react';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';

export interface ShellUser {
    id: number;
    name: string;
    email: string;
}

/**
 * Kullanıcı menüsü. Kimlik modülü (Faz 1, Prompt 03) öncesi auth.user null olabilir;
 * bu durumda menü yerine isimsiz avatar çizilir. Profil/çıkış gibi aksiyonlar
 * yalnızca gerçek rotaları Prompt 03 ile geldiğinde buraya eklenir —
 * çalışmayan buton bırakılmaz.
 */
export function UserMenu({ user }: { user: ShellUser | null }) {
    if (!user) {
        return (
            <div
                className="flex h-8 w-8 items-center justify-center rounded-full bg-surface-muted text-muted-foreground"
                title="Oturum açık değil"
                aria-hidden
            >
                <svg
                    className="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                >
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4 3.6-6 8-6s8 2 8 6" />
                </svg>
            </div>
        );
    }

    const initials = user.name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <DropdownMenuPrimitive.Root>
            <DropdownMenuPrimitive.Trigger
                className={cn(
                    'flex items-center gap-2 rounded-full border border-border bg-surface px-2 py-1 text-sm hover:bg-surface-muted',
                )}
                aria-label="Kullanıcı menüsü"
            >
                <span className="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                    {initials}
                </span>
                <span className="hidden max-w-40 truncate sm:block">{user.name}</span>
                <ChevronDown className="h-3.5 w-3.5 text-muted-foreground" aria-hidden />
            </DropdownMenuPrimitive.Trigger>
            <DropdownMenuPrimitive.Portal>
                <DropdownMenuPrimitive.Content
                    className="z-[100] min-w-48 rounded-lg border border-border bg-surface p-1.5 shadow-[var(--shadow-popover)]"
                    align="end"
                    sideOffset={6}
                >
                    <div className="border-b border-border px-3 py-2">
                        <p className="text-sm font-medium text-foreground">{user.name}</p>
                        <p className="text-xs text-muted-foreground">{user.email}</p>
                    </div>
                    <LogoutItem />
                </DropdownMenuPrimitive.Content>
            </DropdownMenuPrimitive.Portal>
        </DropdownMenuPrimitive.Root>
    );
}

function LogoutItem() {
    const form = useForm({});

    return (
        <DropdownMenuPrimitive.Item asChild>
            <button
                type="button"
                onClick={() => form.post(route('logout'))}
                className="mt-1 flex w-full cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm text-danger outline-none data-[highlighted]:bg-danger-bg"
                disabled={form.processing}
            >
                <LogOut className="h-4 w-4" aria-hidden />
                Çıkış yap
            </button>
        </DropdownMenuPrimitive.Item>
    );
}

