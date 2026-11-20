import { test, expect } from '@playwright/test';

test.describe('Check-Up akışı', () => {
    test('oturum ac → tamamla → sonuc ekrani (kural motoru onerileri)', async ({ page }) => {
        await page.goto('/app/checkups');
        await page.waitForTimeout(600);
        await page.screenshot({ path: 'docs/screenshots/e2e/10-checkups-list.png', fullPage: true });

        await page.getByRole('button', { name: /Yeni oturum/ }).click();
        await page.waitForURL(/checkups\/create/, { timeout: 20_000 });
        await page.waitForTimeout(800);

        // Şablon ve müşteri seçimi (React controlled select — native setter)
        await page.locator('form select').nth(0).evaluate((el) => {
            const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value')?.set;
            const option = el.querySelectorAll('option')[1];
            if (option) {
                setter?.call(el, option.value);
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        await page.locator('form select').nth(1).evaluate((el) => {
            const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value')?.set;
            const option = el.querySelectorAll('option')[1];
            if (option) {
                setter?.call(el, option.value);
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        await page.screenshot({ path: 'docs/screenshots/e2e/11-checkup-create.png', fullPage: true });

        await page.getByRole('button', { name: /Oturum başlat/ }).click();
        await page.waitForURL(/\/app\/checkups\/\d+/, { timeout: 30_000 });
        await page.waitForTimeout(800);
        await page.screenshot({ path: 'docs/screenshots/e2e/12-checkup-form.png', fullPage: true });

        const sessionId = page.url().match(/checkups\/(\d+)/)?.[1];
        console.log('SESSION-ID:', sessionId, 'URL:', page.url());

        // Tamamlama: oturum çerezleriyle form POST (+_method=PATCH spoof — PATCH
        // istekleri bu sandbox'ta PHP-S tarafından 405'e düşürüldüğü için)
        const patchStatus = await page.evaluate(async (id) => {
            const xsrfCookie = document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '';
            const formData = new FormData();
            formData.append('_method', 'PATCH');
            formData.append('answers[cihaz_sayisi]', '60');
            formData.append('answers[sektor]', 'turizm');
            formData.append('answers[erp_kullanimi]', '0');
            const response = await fetch(`/app/checkups/${id}/complete`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': decodeURIComponent(xsrfCookie),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                body: formData,
            });
            return response.status;
        }, sessionId);
        expect([200, 302, 204]).toContain(patchStatus);

        // Sayfayı tazele: badge 'Tamamlandı' + kural motoru önerileri görünür
        await page.reload();
        await page.waitForTimeout(800);
        await expect(page.getByText('Tamamlandı', { exact: true })).toBeVisible({ timeout: 15_000 });
        await expect(page.locator('body')).toContainText('Fırsat önerisi', { timeout: 10_000 });
        await page.screenshot({ path: 'docs/screenshots/e2e/13-checkup-result.png', fullPage: true });
    });
});
