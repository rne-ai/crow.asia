import React from 'react';
import { router } from '@inertiajs/react';
import { ContentRegister, LocaleCode } from '@/types/remotion';

interface InContentRegisterToggleProps {
    locale: LocaleCode;
    register: ContentRegister;
    onRegisterChange?: (register: ContentRegister) => void;
    targetUrlResolver?: (register: ContentRegister) => string;
    className?: string;
    size?: 'sm' | 'md';
}

export const InContentRegisterToggle: React.FC<InContentRegisterToggleProps> = ({
    locale,
    register,
    onRegisterChange,
    targetUrlResolver,
    className = '',
    size = 'md',
}) => {
    // STRICT INVARIANT: Never render register toggle if locale is not 'id'
    if (locale !== 'id') {
        return null;
    }

    const handleSelect = (selectedRegister: ContentRegister) => {
        if (selectedRegister === register) return;

        // Persist preference in cookie/localStorage
        try {
            localStorage.setItem('crow:id-register', selectedRegister);
            document.cookie = `crow:id-register=${selectedRegister}; path=/; max-age=31536000; SameSite=Lax`;
        } catch {
            // Ignore storage write issues
        }

        if (onRegisterChange) {
            onRegisterChange(selectedRegister);
            return;
        }

        if (targetUrlResolver) {
            const nextUrl = targetUrlResolver(selectedRegister);
            router.visit(nextUrl, { preserveScroll: true, preserveState: true });
            return;
        }

        // Default: update query param ?reg=casual|standard with preserveScroll
        const url = new URL(window.location.href);
        url.searchParams.set('reg', selectedRegister);
        router.visit(url.pathname + url.search, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const isCasual = register === 'casual';
    const isStandard = register === 'standard';

    const paddingClass = size === 'sm' ? 'px-2.5 py-1 text-xs' : 'px-3 py-1.5 text-xs sm:text-sm';

    return (
        <div
            role="group"
            aria-label="Pilih Gaya Bahasa Indonesia"
            className={`inline-flex items-center p-1 rounded-xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-2xs ${className}`}
        >
            {/* Opsi Kasual */}
            <button
                type="button"
                onClick={() => handleSelect('casual')}
                aria-pressed={isCasual}
                className={`inline-flex items-center gap-1.5 font-semibold rounded-lg transition-all focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] min-h-[38px] ${paddingClass} ${
                    isCasual
                        ? 'bg-[#FAF8F5] text-[#111113] shadow-xs border border-[#E5E0D5]'
                        : 'text-stone-600 hover:text-[#111113] border border-transparent'
                }`}
            >
                <span
                    className={`w-2 h-2 rounded-full transition-colors ${
                        isCasual ? 'bg-[#F59E0B]' : 'bg-stone-300'
                    }`}
                    aria-hidden="true"
                />
                <span>😎 Kasual <span className="hidden sm:inline font-normal text-stone-500">(Gue/Lo)</span></span>
            </button>

            {/* Opsi Standar */}
            <button
                type="button"
                onClick={() => handleSelect('standard')}
                aria-pressed={isStandard}
                className={`inline-flex items-center gap-1.5 font-semibold rounded-lg transition-all focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] min-h-[38px] ${paddingClass} ${
                    isStandard
                        ? 'bg-[#FAF8F5] text-[#111113] shadow-xs border border-[#E5E0D5]'
                        : 'text-stone-600 hover:text-[#111113] border border-transparent'
                }`}
            >
                <span
                    className={`w-2 h-2 rounded-full transition-colors ${
                        isStandard ? 'bg-[#2563EB]' : 'bg-stone-300'
                    }`}
                    aria-hidden="true"
                />
                <span>👔 Standar <span className="hidden sm:inline font-normal text-stone-500">(Saya/Kamu)</span></span>
            </button>
        </div>
    );
};
