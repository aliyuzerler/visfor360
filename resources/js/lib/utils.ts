import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Tailwind sınıflarını koşullu birleştirir; token dışı çakışmaları temizler.
 */
export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}
