import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface Question {
    key: string;
    label: string;
    type: string;
    required: boolean;
    config: Record<string, unknown> | null;
}

interface Recommendation {
    service_key: string;
    label: string;
    matched_rule_label: string | null;
    reasons: string[];
}

const importToOpportunity = (resultId: number) => {
    router.post(route('checkups.import', resultId), {}, {
        onSuccess: () => toast.success('Öneriler fırsat havuzuna aktarıldı.'),
        onError: () => toast.error('Aktarım yetkiniz yok.'),
    });
};

/** Check-Up form + sonuç: hangi cevap/kural nedeniyle hangi hizmet önerildi. */
export default function CheckupShow({
    session,
    result,
    can,
}: {
    session: {
        id: number;
        status: string;
        template_version: number;
        answers: Record<string, unknown>;
        customer: { id: number; name: string } | null;
        template: { id: number; title: string; version: number; is_example: boolean; questions: Question[] };
    };
    result: {
        id: number;
        recommendations: Recommendation[];
        rule_trace: { rule_id: number | null; label: string | null; matched: boolean; failed_conditions: string[]; invalid_answers: string[] }[];
        engine_version: string;
        deterministic_hash: string;
    } | null;
    can: { complete: boolean; importToOpportunity: boolean };
}) {
    const form = useForm(session.answers as Record<string, string>);

    const setAnswer = (key: string, value: unknown) => form.setData(key, value as never);

    const complete = (e: React.FormEvent) => {
        e.preventDefault();
        router.patch(route('checkups.complete', session.id), { answers: form.data }, {
            onSuccess: () => toast.success('Check-Up tamamlandı.'),
            onError: (errors) => toast.error(errors.answers ?? 'Zorunlu sorular eksik.'),
        });
    };

    const answerValue = (question: Question) => {
        const value = (session.answers[question.key] ?? form.data[question.key]) as unknown;
        return value === undefined || value === null ? '' : String(value);
    };

    return (
        <>
            <Head title="Check-Up" />
            <AppShell title="Check-Up">
                <PageHeader
                    title={`${session.customer?.name ?? '—'} — ${session.template.title}`}
                    description={`Şablon sürümü v${session.template_version} (oturuma kilitlendi)${session.template.is_example ? ' · ÖRNEK şablon — müşteri soru seti bekleniyor' : ''}`}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Check-Up', href: route('checkups.index') },
                        { label: session.customer?.name ?? '—' },
                    ]}
                    actions={<Badge variant={session.status === 'completed' ? 'success' : 'info'}>{session.status === 'completed' ? 'Tamamlandı' : 'Taslak'}</Badge>}
                />

                {can.complete && (
                    <form className="mb-6 space-y-4 rounded-xl border border-border bg-surface p-5" onSubmit={complete}>
                        <h2 className="text-base font-semibold">Sorular</h2>
                        {session.template.questions.map((question) => (
                            <fieldset key={question.key} className="grid gap-2">
                                <legend className="text-sm font-medium">
                                    {question.label}
                                    {question.required && <span aria-hidden> *</span>}
                                </legend>
                                {question.type === 'boolean' ? (
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={answerValue(question) === '1'}
                                            onChange={(e) => setAnswer(question.key, e.target.checked ? 1 : 0)}
                                            className="h-4 w-4"
                                        />
                                        Evet
                                    </label>
                                ) : question.type === 'single_choice' ? (
                                    <select
                                        className="h-9 max-w-sm rounded-md border border-border bg-background px-3 text-sm"
                                        value={answerValue(question)}
                                        onChange={(e) => setAnswer(question.key, e.target.value)}
                                        required={question.required}
                                    >
                                        <option value="">Seçin…</option>
                                        {((question.config?.options as string[]) ?? []).map((option) => (
                                            <option key={option} value={option}>{option}</option>
                                        ))}
                                    </select>
                                ) : (
                                    <input
                                        className="h-9 max-w-sm rounded-md border border-border bg-background px-3 text-sm"
                                        type={question.type === 'number' ? 'number' : 'text'}
                                        value={answerValue(question)}
                                        onChange={(e) => setAnswer(question.key, question.type === 'number' ? Number(e.target.value) : e.target.value)}
                                        required={question.required}
                                        aria-label={question.label}
                                    />
                                )}
                            </fieldset>
                        ))}
                        <Button type="submit">Tamamla</Button>
                    </form>
                )}

                <section aria-label="Sonuç">
                    <h2 className="mb-3 text-base font-semibold">Sonuç ve açıklama</h2>
                    {!result ? (
                        <EmptyState title="Sonuç yok" description="Oturum tamamlandığında öneriler burada görüntülenir." />
                    ) : (
                        <>
                            {can.importToOpportunity && result.recommendations.length > 0 && (
                                <button
                                    type="button"
                                    onClick={() => importToOpportunity(result.id)}
                                    className="mb-3 rounded-md border border-primary px-3 py-1.5 text-sm text-primary hover:bg-primary/10"
                                >
                                    Önerileri fırsat havuzuna aktar
                                </button>
                            )}
                            {result.recommendations.length === 0 ? (
                                <p className="rounded-xl border border-border bg-surface p-4 text-sm text-muted-foreground" role="note">
                                    Kural koşullarına uyan cevap yok; hizmet önerisi üretilmedi.
                                </p>
                            ) : (
                                <ul className="mb-4 space-y-3">
                                    {result.recommendations.map((recommendation, index) => (
                                        <li key={index} className="rounded-xl border border-border bg-surface p-4">
                                            <div className="flex items-center gap-2">
                                                <Badge variant="success">Fırsat önerisi</Badge>
                                                <strong className="text-sm">{recommendation.label}</strong>
                                            </div>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Kural: {recommendation.matched_rule_label ?? '—'} · Yalnız fırsat önerisi üretir; proje/görev otomatik açılmaz.
                                            </p>
                                            <ul className="mt-2 list-disc pl-5 text-xs text-muted-foreground">
                                                {recommendation.reasons.map((reason, reasonIndex) => (
                                                    <li key={reasonIndex}>{reason}</li>
                                                ))}
                                            </ul>
                                        </li>
                                    ))}
                                </ul>
                            )}
                            <details className="rounded-xl border border-border bg-surface p-4">
                                <summary className="cursor-pointer text-sm font-medium">Kural izi (engine: {result.engine_version})</summary>
                                <ul className="mt-2 space-y-1 text-xs text-muted-foreground">
                                    {result.rule_trace.map((trace, index) => (
                                        <li key={index}>
                                            {trace.matched ? '✓' : '✗'} {trace.label ?? `#${trace.rule_id}`}
                                            {trace.failed_conditions.length > 0 && ` — sağlanmayan: ${trace.failed_conditions.join(', ')}`}
                                            {trace.invalid_answers.length > 0 && ` — geçersiz cevap: ${trace.invalid_answers.join(', ')}`}
                                        </li>
                                    ))}
                                </ul>
                                <p className="mt-2 text-xs text-muted-foreground">Deterministik hash: {result.deterministic_hash}</p>
                            </details>
                        </>
                    )}
                </section>
            </AppShell>
        </>
    );
}
