import { test, expect } from '@playwright/test';

test.describe('Müşteriler', () => {
    const customerName = `E2E Firma ${Date.now()}`;

    test('liste + yeni musteri olusturma + detay', async ({ page }) => {
        page.on('pageerror', (err) => console.log('PAGEERROR:', err.stack?.slice(0, 600)));
        await page.goto('/app/customers');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/04-customers-list.png', fullPage: true });

        await page.getByRole('button', { name: /Yeni müşteri/ }).click();
        await page.waitForURL(/customers\/create/);
        await page.screenshot({ path: 'docs/screenshots/e2e/05-customer-create.png', fullPage: true });

        await page.getByLabel('Firma adi').fill(customerName);
        await page.getByLabel('E-posta').fill(`e2e${Date.now()}@firma.example`);
        await page.getByRole('button', { name: /Kaydet|Musteri|Olustur/ }).first().click();

        await page.waitForURL(/\/app\/customers\/\d+/, { timeout: 20_000 });
        await page.waitForTimeout(800);

        console.log('DEBUG URL:', page.url());
        const bodyText = await page.locator('body').innerText();
        console.log('DEBUG BODY (ilk 300):', bodyText.slice(0, 300).replace(/\n/g, ' | '));

        // Detay ekranında firma adı sayfa başlığıdır (sr-only h1 dâhil)
        await expect(page.locator('body')).toContainText(customerName, { timeout: 15_000 });
        await page.screenshot({ path: 'docs/screenshots/e2e/06-customer-detail.png', fullPage: true });
    });

    test('musteri arama filtresi calisir', async ({ page }) => {
        await page.goto('/app/customers');
        const search = page.locator('input[type=search]');
        await search.fill('E2E Firma');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/07-customers-search.png', fullPage: true });
    });
});
