import { defineConfig } from '@playwright/test';

/**
 * E2E test paketi (Playwright): gerçek akışlar üzerinden sistemde gezinir,
 * her ekrandan ekran görüntüsü alır (docs/screenshots/e2e/).
 * Tek çalışma çalışanı (serial) — demo veri sırası korunur.
 */
export default defineConfig({
    testDir: './e2e',
    globalSetup: './e2e/global-setup.ts',
    timeout: 60_000,
    expect: { timeout: 10_000 },
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list']],
    use: {
        baseURL: 'http://127.0.0.1:8000',
        storageState: 'e2e/.auth/admin.json',
        screenshot: 'off', // ekran görüntüleri test içinde bilinçli, isimli alınıyor
        trace: 'retain-on-failure',
        locale: 'tr-TR',
    },
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000/login',
        reuseExistingServer: true,
        timeout: 30_000,
    },
});
