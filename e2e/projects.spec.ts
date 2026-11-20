import { test, expect } from '@playwright/test';

test.describe('Projeler ve Kanban', () => {
    test('proje listesi ve kanban panosu', async ({ page }) => {
        await page.goto('/app/projects');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: 'docs/screenshots/e2e/08-projects-list.png', fullPage: true });

        // Kanban görünümü ayrı rota ise ona git
        const kanbanLink = page.getByRole('link', { name: /Kanban/i }).first();
        if ((await kanbanLink.count()) > 0) {
            await kanbanLink.click();
            await page.waitForLoadState('networkidle');
        } else if (/kanban/.test(page.url()) === false) {
            await page.goto('/app/projects/kanban').catch(() => undefined);
        }
        await page.screenshot({ path: 'docs/screenshots/e2e/09-projects-kanban.png', fullPage: true });
    });
});
