import { usePage } from '@inertiajs/react';
import { LocaleCode } from '@/types/remotion';

type TranslationReplacements = Record<string, string | number>;

export interface UseTranslationReturn {
    t: (key: string, replacements?: TranslationReplacements) => string;
    locale: LocaleCode;
    currency: 'IDR' | 'USD';
    formatPrice: (amountIdr: number, amountUsd?: number | null) => string;
}

export function useTranslation(): UseTranslationReturn {
    const pageProps = usePage().props as unknown as {
        translations?: Record<string, any>;
        locale?: LocaleCode;
        currency?: 'IDR' | 'USD';
    };

    const translations = pageProps?.translations || {};
    const locale = (pageProps?.locale || 'id') as LocaleCode;
    const currency = (pageProps?.currency || (locale === 'id' ? 'IDR' : 'USD')) as 'IDR' | 'USD';

    const t = (key: string, replacements?: TranslationReplacements): string => {
        const segments = key.split('.');
        let current: any = translations;

        for (const segment of segments) {
            if (current && typeof current === 'object' && segment in current) {
                current = current[segment];
            } else {
                current = null;
                break;
            }
        }

        let result = typeof current === 'string' ? current : key;

        if (replacements && typeof result === 'string') {
            for (const [rKey, rVal] of Object.entries(replacements)) {
                result = result.replaceAll(`:${rKey}`, String(rVal));
                result = result.replaceAll(`{${rKey}}`, String(rVal));
            }
        }

        return result;
    };

    const formatPrice = (amountIdr: number, amountUsd?: number | null): string => {
        if (currency === 'USD' && amountUsd !== undefined && amountUsd !== null) {
            return `$${amountUsd.toFixed(2)}`;
        }
        return `Rp${amountIdr.toLocaleString('id-ID')}`;
    };

    return { t, locale, currency, formatPrice };
}
