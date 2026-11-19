import type { LucideIcon } from 'lucide-react';

/**
 * Ekran haritası (docs/SCREEN_MAP.md) ile eşleşen navigasyon konfigürasyonu.
 * - phase: '1' | '2' | 'later' — Faz 2 ekranları navigation'da kontrollü sunulur
 *   ("Faz 2" rozetli, placeholder sayfa); kilitli gizli değil.
 * - route adları Laravel route adlarıyla eşleşir.
 */
export interface NavItem {
    key: string;
    label: string;
    route: string;
    icon: string;
    phase: '1' | '2';
    /** Placeholder fazda sayfa henüz geliştirilmediği için wireframe gösterilir. */
    placeholder?: boolean;
}

export interface NavGroup {
    key: string;
    label: string;
    items: NavItem[];
}

export const NAV_GROUPS: NavGroup[] = [
    {
        key: 'operations',
        label: 'Operasyon',
        items: [
            { key: 'dashboard', label: 'Genel Bakış', route: 'dashboard', icon: 'LayoutDashboard', phase: '1' },
            { key: 'customers', label: 'Müşteriler', route: 'customers.index', icon: 'Building2', phase: '1' },
            { key: 'projects', label: 'Projeler', route: 'projects.index', icon: 'FolderKanban', phase: '1', placeholder: true },
            { key: 'tasks', label: 'Görevler', route: 'tasks.index', icon: 'ListChecks', phase: '1', placeholder: true },
        ],
    },
    {
        key: 'strategy',
        label: 'Strateji ve Finans',
        items: [
            { key: 'goals', label: 'Stratejik Hedefler', route: 'goals.index', icon: 'Target', phase: '1' },
            { key: 'finance', label: 'Finans', route: 'finance.index', icon: 'Wallet', phase: '1' },
        ],
    },
    {
        key: 'growth',
        label: 'Gelişim (Faz 2)',
        items: [
            { key: 'checkups', label: 'Check-Up', route: 'checkups.index', icon: 'ClipboardCheck', phase: '2', placeholder: true },
            { key: 'opportunities', label: 'Fırsat Havuzu', route: 'opportunities.index', icon: 'Lightbulb', phase: '2', placeholder: true },
            { key: 'partners', label: 'Çözüm Ortakları', route: 'partners.index', icon: 'Handshake', phase: '2', placeholder: true },
        ],
    },
    {
        key: 'admin',
        label: 'Yönetim',
        items: [
            { key: 'reports', label: 'Raporlar', route: 'reports.index', icon: 'BarChart3', phase: '1', placeholder: true },
            { key: 'settings', label: 'Ayarlar', route: 'settings.index', icon: 'Settings', phase: '1' },
        ],
    },
];
