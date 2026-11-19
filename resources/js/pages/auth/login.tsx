import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { Head, Link, useForm } from '@inertiajs/react';
import { route } from '@/lib/route';

/**
 * Giriş ekranı. Hata mesajları hesap varlığı hakkında bilgi sızdırmaz.
 * Brute-force koruması sunucu tarafında RateLimiter ile yapılır.
 */
export default function Login({ status }: { status?: string }) {
    const form = useForm<{ email: string; password: string; remember: boolean }>({
        email: '',
        password: '',
        remember: false,
    });

    return (
        <>
            <Head title="Giriş" />
            <main className="flex min-h-screen items-center justify-center bg-background px-4">
                <div className="w-full max-w-sm">
                    <div className="mb-6 text-center">
                        <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-base font-bold text-primary-foreground">
                            V
                        </div>
                        <h1 className="text-lg font-semibold text-foreground">Visfor360</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Bütünleşik danışmanlık ve işletme yönetim platformu
                        </p>
                    </div>
                    <form
                        className="space-y-4 rounded-xl border border-border bg-surface p-6 shadow-[var(--shadow-card)]"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.post(route('login'), {
                                onFinish: () => form.reset('password'),
                            });
                        }}
                    >
                        {status && (
                            <p className="rounded-md bg-success-bg px-3 py-2 text-sm text-success" role="status">
                                {status}
                            </p>
                        )}
                        <FormField name="email" label="E-posta" error={form.errors.email} required>
                            {(props) => (
                                <Input
                                    {...props}
                                    type="email"
                                    autoComplete="email"
                                    value={form.data.email}
                                    onChange={(e) => form.setData('email', e.target.value)}
                                    autoFocus
                                />
                            )}
                        </FormField>
                        <FormField name="password" label="Parola" error={form.errors.password} required>
                            {(props) => (
                                <Input
                                    {...props}
                                    type="password"
                                    autoComplete="current-password"
                                    value={form.data.password}
                                    onChange={(e) => form.setData('password', e.target.value)}
                                />
                            )}
                        </FormField>
                        <label className="flex items-center gap-2 text-sm text-muted-foreground">
                            <input
                                type="checkbox"
                                className="h-4 w-4 accent-[var(--color-primary)]"
                                checked={form.data.remember}
                                onChange={(e) => form.setData('remember', e.target.checked)}
                            />
                            Beni hatırla
                        </label>
                        <Button type="submit" loading={form.processing} className="w-full">
                            Giriş yap
                        </Button>
                        <p className="text-center text-sm text-muted-foreground">
                            <Link href={route('password.request')} className="text-primary hover:underline">
                                Parolanızı mı unuttunuz?
                            </Link>
                        </p>
                    </form>
                </div>
            </main>
        </>
    );
}
