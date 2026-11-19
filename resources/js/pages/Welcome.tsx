import { Head, Link } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Hoş geldiniz" />
            <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
                <div className="w-full max-w-md rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white">
                        V
                    </div>
                    <h1 className="text-xl font-semibold text-slate-900">
                        Visfor360
                    </h1>
                    <p className="mt-2 text-sm text-slate-600">
                        Bütünleşik danışmanlık ve işletme yönetim platformu. Kimlik modülü
                        tamamlanana kadar yönetim ekranları wireframe olarak sunulur.
                    </p>
                    <div className="mt-5">
                        <Link
                            href="/app/dashboard"
                            className="inline-flex h-9 items-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Uygulamaya git
                        </Link>
                    </div>
                </div>
            </main>
        </>
    );
}
