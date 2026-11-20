import { chromium, type FullConfig } from '@playwright/test';

/**
 * Global kurulum: yönetici ve portal kullanıcısı için gerçek login akışıyla
 * storage state üretir. Rate limiter'a tek istek düşer; testler state ile açılır.
 */
async function loginAndSave(
    baseURL: string,
    path: string,
    loginUrl: string,
    selectors: { email: string; password: string; submit: string },
    credentials: { email: string; password: string },
    expectUrl: string,
): Promise<void> {
    const browser = await chromium.launch();
    const context = await browser.newContext({ locale: 'tr-TR' });
    const page = await context.newPage();

    await page.goto(loginUrl);
    await page.fill(selectors.email, credentials.email);
    await page.fill(selectors.password, credentials.password);
    await page.click(selectors.submit);
    await page.waitForURL(expectUrl, { timeout: 20_000 });

    await context.storageState({ path });
    await browser.close();
}

export default async function globalSetup(_config: FullConfig): Promise<void> {
    const baseURL = 'http://127.0.0.1:8000';

    await loginAndSave(
        baseURL,
        'e2e/.auth/admin.json',
        `${baseURL}/login`,
        { email: 'input[type=email]', password: 'input[type=password]', submit: 'button[type=submit]' },
        { email: 'admin@visfor.example', password: 'Password123456' },
        /\/app\/dashboard/,
    );

    await loginAndSave(
        baseURL,
        'e2e/.auth/portal.json',
        `${baseURL}/portal/login`,
        { email: 'input[type=email]', password: 'input[type=password]', submit: 'button[type=submit]' },
        { email: 'e2e@firmaya.example', password: 'Password123456' },
        /\/portal/,
    );
}
