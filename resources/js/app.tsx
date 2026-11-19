import { createInertiaApp, type ResolvedComponent } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';

createInertiaApp({
    progress: { color: '#2563eb' },
    title: (title) => `${title ? `${title} - ` : ''}Visfor360`,
    resolve: (name): ResolvedComponent => {
        const pages = import.meta.glob<{ default: ResolvedComponent }>('./pages/**/*.tsx', {
            eager: true,
        });
        const page = pages[`./pages/${name}.tsx`];
        if (!page) {
            throw new Error(`Sayfa bulunamadı: ${name}`);
        }
        return page.default;
    },
    setup({ el, App, props }) {
        if (el) {
            createRoot(el).render(<App {...props} />);
        }
    },
});
