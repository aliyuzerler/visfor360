import { route as ziggyRoute } from 'ziggy-js';

/**
 * Laravel route adlarını URL'e çevirir (Ziggy).
 * Ziggy konfigürasyonu app.blade.php içindeki @routes direktifiyle sağlanır.
 * absolute=false → "/customers" gibi path döner.
 */
export function route(name: string, params?: Parameters<typeof ziggyRoute>[1], absolute?: boolean): string {
    // Ziggy, params yokken zincirlenebilir Router nesnesi döndürebilir; string'e çevrilir.
    return String(ziggyRoute(name, params, absolute));
}
