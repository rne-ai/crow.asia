import React from 'react';
import { usePage } from '@inertiajs/react';
import { BookId, LocaleCode } from '@/types/remotion';
import { GlobalLocaleMenu, SupportedLocaleItem } from './GlobalLocaleMenu';
import { PaperTheme } from '@/Layouts/FullWidthWorkspaceLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { LogIn, Search, User as UserIcon } from 'lucide-react';

interface TopNavbarFullWidthProps {
    currentLocale: LocaleCode;
    supportedLocales?: SupportedLocaleItem[];
    onLocaleChange?: (locale: LocaleCode) => void;
    currentBookId?: BookId;
    paperTheme?: PaperTheme;
    onTogglePaperTheme?: (theme: PaperTheme) => void;
    onOpenSearch?: () => void;
    user?: { name: string; email: string; role?: string } | null;
    showDashboardLink?: boolean;
    extraActions?: React.ReactNode;
}

const BOOKS = [
    { id: 'book-1' as const, label: 'VOL. 01', accent: '#1D4ED8' },
    { id: 'book-2' as const, label: 'VOL. 02', accent: '#EA580C' },
    { id: 'book-3' as const, label: 'VOL. 03', accent: '#0F766E' },
];

export const TopNavbarFullWidth: React.FC<TopNavbarFullWidthProps> = ({
    currentLocale,
    supportedLocales,
    onLocaleChange,
    currentBookId = 'book-1',
    paperTheme = 'ivory',
    onTogglePaperTheme,
    onOpenSearch,
    user,
    showDashboardLink = true,
    extraActions,
}) => {
    const { t } = useTranslation();
    const pageProps = usePage().props as unknown as { auth?: { user?: { name: string; email: string } | null } };
    const currentUser = user || pageProps?.auth?.user || null;

    const nextTheme: PaperTheme =
        paperTheme === 'ivory' ? 'sepia' : paperTheme === 'sepia' ? 'obsidian' : 'ivory';

    const themeLabel =
        paperTheme === 'ivory' ? 'Ivory' : paperTheme === 'sepia' ? 'Sepia' : 'Obsidian';

    return (
        <div className="w-full flex items-center justify-between gap-3 min-w-0">
            {/* Left: Brand & Volume Selector */}
            <div className="flex items-center gap-4 sm:gap-6 min-w-0">
                <a
                    href="/"
                    className="flex items-center gap-2 font-bold text-base sm:text-lg text-[#111113] tracking-tight shrink-0 focus-visible:outline-2 focus-visible:outline-[#1D4ED8] rounded-md px-1"
                >
                    <span className="text-xl" role="img" aria-label="Crow logo">🦅</span>
                    <span>crow<span className="text-[#1D4ED8]">.asia</span></span>
                </a>

                {/* Volume Selector Tabs */}
                <nav className="hidden md:flex items-center gap-1.5 font-mono text-xs" aria-label="Volume Selector">
                    {BOOKS.map((b) => {
                        const isActive = b.id === currentBookId;
                        const bookTitle = t(`nav.book_titles.${b.id}`);
                        return (
                            <a
                                key={b.id}
                                href={`/read/${b.id}/${currentLocale}/ch-0`}
                                className={`px-2.5 py-1 rounded-md transition-colors flex items-center gap-1.5 ${
                                    isActive
                                        ? 'bg-[#111113] text-[#FAF8F5] font-bold shadow-2xs'
                                        : 'bg-[#FAF8F5] text-stone-600 hover:text-[#111113] border border-[#E5E0D5]'
                                }`}
                                title={bookTitle}
                            >
                                <span
                                    className="w-1.5 h-1.5 rounded-full"
                                    style={{ backgroundColor: b.accent }}
                                    aria-hidden="true"
                                />
                                <span>{b.label}</span>
                            </a>
                        );
                    })}
                </nav>
            </div>

            {/* Middle: SOS Topik (Ctrl+K) */}
            <div className="flex-1 max-w-xs hidden sm:flex items-center justify-center">
                {onOpenSearch && (
                    <button
                        type="button"
                        onClick={onOpenSearch}
                        className="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-xs rounded-lg border border-[#E5E0D5] bg-[#FFFFFF] text-stone-500 hover:text-[#111113] hover:border-stone-400 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] min-h-[38px]"
                    >
                        <span className="flex items-center gap-1.5 truncate">
                            <Search className="w-3.5 h-3.5 text-stone-400 shrink-0" />
                            <span className="truncate">{t('nav.search_placeholder')}</span>
                        </span>
                        <kbd className="hidden md:inline-block font-mono text-[10px] bg-stone-100 border border-stone-200 px-1.5 py-0.5 rounded text-stone-600">
                            Ctrl+K
                        </kbd>
                    </button>
                )}
            </div>

            {/* Right: Actions, Theme, Language Menu, Dashboard */}
            <div className="flex items-center gap-2 sm:gap-3 shrink-0">
                {extraActions}

                {/* Paper Theme Quick Switcher */}
                {onTogglePaperTheme && (
                    <button
                        type="button"
                        onClick={() => onTogglePaperTheme(nextTheme)}
                        title={`${t('nav.change_theme')} (${themeLabel})`}
                        aria-label={`${t('nav.change_theme')}, ${themeLabel}`}
                        className="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-[#E5E0D5] bg-[#FFFFFF] text-xs font-mono text-stone-700 hover:bg-[#FAF8F5] focus-visible:outline-2 focus-visible:outline-[#1D4ED8] transition-colors min-h-[38px]"
                    >
                        <span className="text-xs">🎨</span>
                        <span className="hidden sm:inline">{themeLabel}</span>
                    </button>
                )}

                {/* Global Locale Menu */}
                <GlobalLocaleMenu
                    currentLocale={currentLocale}
                    supportedLocales={supportedLocales}
                    onLocaleChange={onLocaleChange}
                />

                {/* User / Auth Link */}
                {currentUser ? (
                    <a
                        href="/dashboard"
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-[#111113] text-[#FAF8F5] hover:bg-stone-800 focus-visible:outline-2 focus-visible:outline-[#1D4ED8] transition-colors min-h-[38px]"
                        title={`${t('nav.dashboard')} (${currentUser.email})`}
                    >
                        <UserIcon className="w-3.5 h-3.5" />
                        <span className="hidden md:inline">{t('nav.dashboard')}</span>
                    </a>
                ) : (
                    <a
                        href="/login"
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-lg border border-[#E5E0D5] bg-[#FFFFFF] text-[#111113] hover:bg-[#FAF8F5] focus-visible:outline-2 focus-visible:outline-[#1D4ED8] transition-colors min-h-[38px]"
                    >
                        <LogIn className="w-3.5 h-3.5 text-stone-500" />
                        <span>{t('nav.login')}</span>
                    </a>
                )}
            </div>
        </div>
    );
};
