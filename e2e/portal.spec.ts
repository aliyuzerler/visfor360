import { test, expect } from '@playwright/test';

test.describe('Müşteri Portalı', () => {
    test.use({ storageState: 'e2e/.auth/portal.json' });

    test('portal anasayfasi: projeler + belge talepleri', async ({ page }) => {
        await page.goto('/portal');
        await expect(page.locator('body')).toContainText('Müşteri Portalı');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/20-portal.png', fullPage: true });
    });

    test('portal izolasyonu: ic moduller acilmaz', async ({ page }) => {
        await page.goto('/app/customers');
        await expect(page).toHaveURL(/login/);
    });
});
