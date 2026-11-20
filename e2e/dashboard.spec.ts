import { test, expect } from '@playwright/test';

test.describe('Genel Bakış (dashboard)', () => {
    test('KPI kartlari ve sidebar gorunur', async ({ page }) => {
        await page.goto('/app/dashboard');
        await expect(page.locator('header')).toBeVisible();
        await expect(page.locator('aside')).toBeVisible();
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/02-dashboard.png', fullPage: true });

        // Footer gorunur
        await expect(page.locator('footer')).toContainText('Visfor360');
    });

    test('mobil gorunumde icerik tasmaz', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/app/dashboard');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/03-dashboard-mobile.png', fullPage: true });

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow).toBeLessThanOrEqual(2);
    });
});
