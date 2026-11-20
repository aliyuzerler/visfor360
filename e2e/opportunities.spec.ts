import { test, expect } from '@playwright/test';

test.describe('Fırsat Havuzu ve Ortaklar', () => {
    test('firsat listesi ve detay', async ({ page }) => {
        await page.goto('/app/opportunities');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/14-opportunities-list.png', fullPage: true });

        const detail = page.locator('a[href*="/app/opportunities/"]').first();
        if (await detail.count() > 0) {
            await detail.click();
            await page.waitForLoadState('networkidle');
            await page.screenshot({ path: 'docs/screenshots/e2e/15-opportunity-detail.png', fullPage: true });
        }
    });

    test('cozum ortaklari liste ve detay', async ({ page }) => {
        await page.goto('/app/partners');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/16-partners-list.png', fullPage: true });

        const detail = page.locator('a[href*="/app/partners/"]').first();
        if (await detail.count() > 0) {
            await detail.click();
            await page.waitForLoadState('networkidle');
            await page.screenshot({ path: 'docs/screenshots/e2e/17-partner-detail.png', fullPage: true });
        }
    });
});
