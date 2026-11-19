import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { Head, Link, useForm } from '@inertiajs/react';
import { route } from '@/lib/route';

/** Parola sıfırlama bağlantısı isteme ekranı. */
export default function ForgotPassword({ status }: { status?: string }) {
    const form = useForm({ email: '' });

    return (
        <>
            <Head title="Parolayı sıfırla" />
            <main className="flex min-h-screen items-center justify-center bg-background px-4">
                <div className="w-full max-w-sm">
                    <form
                        className="space-y-4 rounded-xl border border-border bg-surface p-6 shadow-[var(--shadow-card)]"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.post(route('password.email'));
                        }}
                    >
                        <h1 className="text-lg font-semibold text-foreground">Parolayı sıfırla</h1>
                        <p className="text-sm text-muted-foreground">
                            E-posta adresinizi girin; sıfırlama bağlantısını size gönderelim.
                        </p>
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
                        <Button type="submit" loading={form.processing} className="w-full">
                            Sıfırlama bağlantısı gönder
                        </Button>
                        <p className="text-center text-sm text-muted-foreground">
                            <Link href={route('login')} className="text-primary hover:underline">
                                Girişe dön
                            </Link>
                        </p>
                    </form>
                </div>
            </main>
        </>
    );
}
