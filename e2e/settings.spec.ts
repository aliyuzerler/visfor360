import { test, expect } from '@playwright/test';

test.describe('Ayarlar', () => {
    test('ayarlar hub ve checkup sablonlari', async ({ page }) => {
        await page.goto('/app/settings');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/18-settings.png', fullPage: true });

        await page.goto('/app/settings/checkups');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/19-settings-checkups.png', fullPage: true });
        await expect(page.locator('footer')).toBeVisible();
    });
});
