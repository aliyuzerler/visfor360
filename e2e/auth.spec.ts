import { test, expect } from '@playwright/test';

test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Kimlik ve oturum', () => {
    test('giris sayfasi goruntulenir ve giris akisi calisir', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('input[type=email]')).toBeVisible();
        await page.screenshot({ path: 'docs/screenshots/e2e/01-login.png', fullPage: true });

        await page.fill('input[type=email]', 'admin@visfor.example');
        await page.fill('input[type=password]', 'Password123456');
        await page.click('button[type=submit]');
        await page.waitForURL(/\/app\/dashboard/, { timeout: 30_000 });
        await expect(page).toHaveURL(/dashboard/);
        await page.screenshot({ path: 'docs/screenshots/e2e/01b-after-login.png', fullPage: true });
    });
});
