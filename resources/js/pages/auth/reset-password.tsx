import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { Head, useForm } from '@inertiajs/react';
import { route } from '@/lib/route';

/** Yeni parola belirleme ekranı (tek kullanımlık token). */
export default function ResetPassword({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    const form = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    return (
        <>
            <Head title="Yeni parola" />
            <main className="flex min-h-screen items-center justify-center bg-background px-4">
                <form
                    className="w-full max-w-sm space-y-4 rounded-xl border border-border bg-surface p-6 shadow-[var(--shadow-card)]"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(route('password.update'));
                    }}
                >
                    <h1 className="text-lg font-semibold text-foreground">Yeni parola belirleyin</h1>
                    <p className="text-sm text-muted-foreground">
                        En az 12 karakter, harf ve sayı içermelidir. Kaydedildiğinde eski oturumlarınız kapatılır.
                    </p>
                    <FormField name="email" label="E-posta" error={form.errors.email} required>
                        {(props) => (
                            <Input
                                {...props}
                                type="email"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                autoFocus
                            />
                        )}
                    </FormField>
                    <FormField
                        name="password"
                        label="Yeni parola"
                        hint="En az 12 karakter; harf ve sayı içerir."
                        error={form.errors.password}
                        required
                    >
                        {(props) => (
                            <Input
                                {...props}
                                type="password"
                                autoComplete="new-password"
                                value={form.data.password}
                                onChange={(e) => form.setData('password', e.target.value)}
                            />
                        )}
                    </FormField>
                    <FormField name="password_confirmation" label="Parolayı tekrar yazın" required>
                        {(props) => (
                            <Input
                                {...props}
                                type="password"
                                autoComplete="new-password"
                                value={form.data.password_confirmation}
                                onChange={(e) => form.setData('password_confirmation', e.target.value)}
                            />
                        )}
                    </FormField>
                    <Button type="submit" loading={form.processing} className="w-full">
                        Parolayı güncelle
                    </Button>
                </form>
            </main>
        </>
    );
}
