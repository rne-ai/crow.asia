import React, { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import MarketingFullWidthLayout from '@/Layouts/MarketingFullWidthLayout';
import { SupportedLocaleItem } from '@/Components/navigation/GlobalLocaleMenu';
import { HeroTrilogy16x9Comp } from '@/Components/remotion/compositions/HeroTrilogy16x9Comp';
import { RemotionBookCoverPlayer } from '@/Components/reader/RemotionBookCoverPlayer';
import { BookId, LocaleCode } from '@/types/remotion';
import { Player, PlayerRef } from '@remotion/player';
import { useTranslation } from '@/hooks/useTranslation';
import {
    ArrowRight,
    BookOpen,
    Check,
    CheckCircle2,
    ChevronDown,
    HelpCircle,
    RotateCcw,
    ShieldCheck,
    Sparkles,
} from 'lucide-react';

interface BookItem {
    id: BookId;
    volumeNumber: number;
    volumeCode: string;
    title: string;
    subtitle: string;
    accentHex: string;
    priceSingleIdr: number;
    priceSingleUsd: number;
    targetRoles: string[];
    keyFrameworks: string[];
}

interface PricingTierItem {
    id: string;
    name: string;
    description: string;
    features: string[];
    badges: { label?: string; color?: string } | null;
    priceIdr: number;
    strikePriceIdr: number | null;
    priceUsd: number;
    includedBookIds: string[];
    isBestValue: boolean;
}

interface LandingProps {
    locale: LocaleCode;
    supportedLocales: SupportedLocaleItem[];
    books: BookItem[];
    pricingTiers: PricingTierItem[];
    user: { name: string; email: string; role?: string } | null;
}

export default function Landing({
    locale,
    supportedLocales,
    books,
    pricingTiers,
    user,
}: LandingProps) {
    const { t, currency, formatPrice } = useTranslation();
    const [activeVolume, setActiveVolume] = useState<BookId>('book-1');
    const [hoveredBookId, setHoveredBookId] = useState<BookId | null>(null);
    const [mounted, setMounted] = useState(false);

    // Kuis Diagnosis State (3 Questions)
    const [quizAnswers, setQuizAnswers] = useState<Record<number, BookId | null>>({
        1: null,
        2: null,
        3: null,
    });
    const [quizCurrentStep, setQuizCurrentStep] = useState(1);

    // FAQ Accordion State
    const [expandedFaq, setExpandedFaq] = useState<number | null>(0);

    const playerRef = useRef<PlayerRef>(null);

    useEffect(() => {
        setMounted(true);
    }, []);

    // Calculate Quiz Recommendation
    const quizResultBookId: BookId = (() => {
        const votes = { 'book-1': 0, 'book-2': 0, 'book-3': 0 };
        Object.values(quizAnswers).forEach((b) => {
            if (b) votes[b]++;
        });
        if (votes['book-3'] >= votes['book-1'] && votes['book-3'] >= votes['book-2']) return 'book-3';
        if (votes['book-2'] > votes['book-1']) return 'book-2';
        return 'book-1';
    })();

    const recommendedBook = books.find((b) => b.id === quizResultBookId) || books[0];

    const heroDesc = t('landing.hero.desc');

    const faqItems = [
        { q: t('landing.faq.items.0.q'), a: t('landing.faq.items.0.a') },
        { q: t('landing.faq.items.1.q'), a: t('landing.faq.items.1.a') },
        { q: t('landing.faq.items.2.q'), a: t('landing.faq.items.2.a') },
        { q: t('landing.faq.items.3.q'), a: t('landing.faq.items.3.a') },
    ];

    return (
        <MarketingFullWidthLayout currentLocale={locale}>
            <Head title="crow.asia — Pan-Asian Interactive Leadership Field Playbook Platform" />

            {/* 1. HERO SECTION (Split 50/50 Desktop Full-Width) */}
            <section className="w-full border-b border-[#E5E0D5] bg-[#FAF8F5] py-12 lg:py-16 px-4 sm:px-6 lg:px-10">
                <div className="w-full max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12">
                    {/* Left Column: Value Prop */}
                    <div className="flex-1 space-y-6">
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-[#E5E0D5] bg-[#FFFFFF] text-xs font-mono font-bold text-stone-700 shadow-2xs">
                                <span className="w-2 h-2 rounded-full bg-[#1D4ED8]" />
                                <span>{t('landing.badge')}</span>
                            </div>
                        </div>

                        <h1 className="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-[#111113] leading-[1.18]">
                            {t('landing.hero.title_prefix')}{' '}
                            <mark className="crow-hero-mark">
                                {t('landing.hero.title_mark')}
                            </mark>{' '}
                            {t('landing.hero.title_middle')}{' '}
                            <span className="crow-scribble-container">
                                <span>{t('landing.hero.title_scribble')}</span>
                                <svg
                                    className="crow-scribble-svg"
                                    viewBox="0 0 240 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M3 15C45 4 150 2 236 12C180 18 100 22 25 19C70 14 175 16 230 18"
                                        stroke="#059669"
                                        strokeWidth="3.2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        className="crow-scribble-path"
                                    />
                                </svg>
                            </span>{' '}
                            {t('landing.hero.title_suffix')}
                        </h1>

                        <p className="text-base sm:text-lg text-stone-600 max-w-xl leading-relaxed">
                            {heroDesc}
                        </p>

                        <div className="flex flex-wrap items-center gap-4 pt-2">
                            <a
                                href={`/read/book-1/${locale}/ch-0`}
                                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#111113] text-[#FAF8F5] font-bold text-sm hover:bg-stone-800 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] shadow-xs min-h-[44px]"
                            >
                                <BookOpen className="w-4 h-4" />
                                <span>{t('landing.hero.cta_read_free')}</span>
                            </a>
                            <a
                                href="#harga"
                                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl border border-[#E5E0D5] bg-[#FFFFFF] text-[#111113] font-bold text-sm hover:bg-stone-50 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] shadow-2xs min-h-[44px]"
                            >
                                <span>{t('landing.hero.cta_view_bundle')}</span>
                                <ArrowRight className="w-4 h-4 text-stone-400" />
                            </a>
                        </div>
                    </div>

                    {/* Right Column: Interactive Remotion 16:9 Showcase */}
                    <div className="w-full lg:w-1/2 flex flex-col gap-3">
                        <div className="w-full rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] p-2.5 sm:p-3 shadow-sm">
                            <div className="relative w-full overflow-hidden rounded-xl bg-black" style={{ aspectRatio: '16 / 9' }}>
                                {!mounted ? (
                                    <div className="flex h-full w-full items-center justify-center font-mono text-xs text-stone-400">
                                        {t('landing.hero.showcase_loading')}
                                    </div>
                                ) : (
                                    <Player
                                        ref={playerRef}
                                        component={HeroTrilogy16x9Comp}
                                        inputProps={{
                                            activeVolume,
                                            locale,
                                        }}
                                        durationInFrames={540}
                                        fps={30}
                                        compositionWidth={1280}
                                        compositionHeight={720}
                                        style={{ width: '100%', height: '100%' }}
                                        controls
                                        autoPlay={false}
                                        loop
                                    />
                                )}
                            </div>
                        </div>

                        {/* Volume Switcher Tabs for 16:9 Player */}
                        <div className="flex items-center justify-center gap-2 font-mono text-xs">
                            <span className="text-stone-400 hidden sm:inline">{t('landing.hero.preview_animation')}</span>
                            {books.map((b) => (
                                <button
                                    key={b.id}
                                    type="button"
                                    onClick={() => setActiveVolume(b.id)}
                                    className={`px-3 py-1.5 rounded-lg border transition-all flex items-center gap-1.5 min-h-[36px] ${
                                        activeVolume === b.id
                                            ? 'bg-[#111113] text-white border-[#111113] font-bold shadow-2xs'
                                            : 'bg-white text-stone-600 border-[#E5E0D5] hover:border-stone-400'
                                    }`}
                                >
                                    <span
                                        className="w-2 h-2 rounded-full"
                                        style={{ backgroundColor: b.accentHex }}
                                    />
                                    <span>{b.volumeCode}</span>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* 2. KUIS DIAGNOSIS TAKTIS 45 DETIK */}
            <section id="diagnosis" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FFFFFF]">
                <div className="w-full max-w-4xl mx-auto space-y-8">
                    <div className="text-center space-y-2">
                        <span className="inline-flex items-center gap-1.5 font-mono text-xs font-bold uppercase tracking-wider text-[#1D4ED8] bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                            <span>✨</span>
                            <span>{t('landing.quiz.header_badge')}</span>
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.quiz.title')}
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            {t('landing.quiz.desc')}
                        </p>
                    </div>

                    <div className="p-6 sm:p-8 rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-sm space-y-6">
                        {quizCurrentStep <= 3 && (
                            /* Top Bar inside Quiz Card */
                            <div className="flex items-center justify-between pb-4 border-b border-[#E5E0D5]/70">
                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-blue-200 bg-blue-50/50 text-xs font-semibold text-[#1D4ED8]">
                                    <span>✨</span>
                                    <span>{t('landing.quiz.card_badge')}</span>
                                </span>

                                <div className="flex items-center gap-2.5 text-xs font-mono text-stone-500">
                                    <span>{t('landing.quiz.step_indicator', { current: quizCurrentStep, total: 3 })}</span>
                                    <div className="w-16 h-2 rounded-full bg-stone-100 border border-stone-200 overflow-hidden">
                                        <div
                                            className="h-full bg-[#111113] rounded-full transition-all duration-300"
                                            style={{ width: `${(quizCurrentStep / 3) * 100}%` }}
                                        />
                                    </div>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 1 && (
                            <div className="space-y-4">
                                <h3 className="font-extrabold text-base sm:text-lg text-[#111113]">
                                    {t('landing.quiz.step_1_q')}
                                </h3>
                                <div className="space-y-2.5">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 1: 'book-1' }));
                                            setQuizCurrentStep(2);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                A
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_1_o1')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 1: 'book-2' }));
                                            setQuizCurrentStep(2);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                B
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_1_o2')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 1: 'book-3' }));
                                            setQuizCurrentStep(2);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                C
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_1_o3')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 2 && (
                            <div className="space-y-4">
                                <h3 className="font-extrabold text-base sm:text-lg text-[#111113]">
                                    {t('landing.quiz.step_2_q')}
                                </h3>
                                <div className="space-y-2.5">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 2: 'book-1' }));
                                            setQuizCurrentStep(3);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                A
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_2_o1')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 2: 'book-2' }));
                                            setQuizCurrentStep(3);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                B
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_2_o2')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 2: 'book-3' }));
                                            setQuizCurrentStep(3);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                C
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_2_o3')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 3 && (
                            <div className="space-y-4">
                                <h3 className="font-extrabold text-base sm:text-lg text-[#111113]">
                                    {t('landing.quiz.step_3_q')}
                                </h3>
                                <div className="space-y-2.5">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 3: 'book-1' }));
                                            setQuizCurrentStep(4);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                A
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_3_o1')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 3: 'book-2' }));
                                            setQuizCurrentStep(4);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                B
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_3_o2')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 3: 'book-3' }));
                                            setQuizCurrentStep(4);
                                        }}
                                        className="w-full text-left p-3.5 sm:p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#111113] hover:bg-[#FAF8F5] transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[48px] flex items-center justify-between group shadow-2xs"
                                    >
                                        <span className="flex items-center gap-3 min-w-0">
                                            <span className="w-7 h-7 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] group-hover:bg-white group-hover:border-[#111113] flex items-center justify-center font-mono font-bold text-xs text-stone-700 shrink-0 transition-colors">
                                                C
                                            </span>
                                            <span className="leading-snug">{t('landing.quiz.step_3_o3')}</span>
                                        </span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 group-hover:text-[#111113] group-hover:translate-x-0.5 transition-all shrink-0 ml-3" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 4 && (
                            /* Result Card */
                            <div className="p-6 rounded-xl border-2 border-[#111113] bg-white space-y-5 animate-in fade-in duration-200">
                                <div className="flex items-center justify-between">
                                    <span
                                        className="px-2.5 py-1 rounded text-xs font-mono font-bold text-white uppercase"
                                        style={{ backgroundColor: recommendedBook.accentHex }}
                                    >
                                        {t('landing.quiz.result_badge')}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers({ 1: null, 2: null, 3: null });
                                            setQuizCurrentStep(1);
                                        }}
                                        className="text-xs font-mono text-stone-500 hover:text-stone-900 flex items-center gap-1"
                                    >
                                        <RotateCcw className="w-3.5 h-3.5" />
                                        <span>{t('landing.quiz.cta_repeat_quiz')}</span>
                                    </button>
                                </div>

                                <div className="space-y-2">
                                    <h3 className="text-xl sm:text-2xl font-black text-[#111113]">
                                        {t('landing.quiz.result_title', { volume: recommendedBook.volumeCode })}: {recommendedBook.title}
                                    </h3>
                                    <p className="text-sm text-stone-600 leading-relaxed">
                                        {recommendedBook.subtitle}
                                    </p>
                                </div>

                                <div className="flex flex-wrap gap-2 text-xs font-mono">
                                    <span className="bg-[#FAF8F5] border border-[#E5E0D5] px-2.5 py-1 rounded text-stone-700">
                                        {t('landing.quiz.frameworks_label', { frameworks: recommendedBook.keyFrameworks.join(' • ') })}
                                    </span>
                                </div>

                                <div className="pt-2 flex flex-wrap gap-3">
                                    <a
                                        href={`/read/${recommendedBook.id}/${locale}/ch-0`}
                                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#111113] text-white text-xs sm:text-sm font-bold hover:bg-stone-800 transition-colors"
                                    >
                                        <BookOpen className="w-4 h-4" />
                                        <span>{t('landing.quiz.cta_open_recommended')}</span>
                                    </a>
                                    <a
                                        href="#harga"
                                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] text-stone-800 text-xs sm:text-sm font-bold hover:bg-stone-100 transition-colors"
                                    >
                                        <span>{t('landing.pricing.cta_buy')} ({formatPrice(recommendedBook.priceSingleIdr, recommendedBook.priceSingleUsd)})</span>
                                    </a>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            {/* 3. KATALOG 3 VOLUME PLAYBOOK (Image 3) */}
            <section id="katalog" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FAF8F5]">
                <div className="w-full max-w-6xl mx-auto space-y-10">
                    <div className="text-center space-y-2">
                        <span className="inline-flex items-center gap-1.5 font-mono text-xs font-bold uppercase tracking-wider text-stone-600 bg-white px-3 py-1 rounded-full border border-[#E5E0D5]">
                            <span>📚</span>
                            <span>{t('landing.catalog.badge')}</span>
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.catalog.title')}
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            {t('landing.catalog.desc')}
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                        {books.map((b) => {
                            const isHovered = hoveredBookId === b.id;

                            return (
                                <div
                                    key={b.id}
                                    onMouseEnter={() => setHoveredBookId(b.id)}
                                    onMouseLeave={() => setHoveredBookId(null)}
                                    className="rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] p-5 sm:p-6 flex flex-col justify-between space-y-5 hover:border-stone-400 transition-all shadow-sm group"
                                >
                                    <div className="space-y-4">
                                        {/* Remotion Animated Book Cover with Hover Trigger */}
                                        <div className="w-full flex justify-center pb-2">
                                            <RemotionBookCoverPlayer
                                                isHovered={isHovered}
                                                bookProps={{
                                                    bookId: b.id,
                                                    volumeCode: b.volumeCode,
                                                    title: b.title,
                                                    subtitle: b.subtitle,
                                                    accentHex: b.accentHex,
                                                    illustrationPath: `/illustrations/${b.id}/portrait.png`,
                                                    coverTitleTop: t(`landing.catalog.cover_titles.${b.id}.top`),
                                                    coverTitleBottom: t(`landing.catalog.cover_titles.${b.id}.bottom`),
                                                    chaptersLabel: t('landing.catalog.chapters_count', { count: 15 }),
                                                    editionLabel: 'ID + EN',
                                                }}
                                                className="w-full max-w-[280px]"
                                            />
                                        </div>

                                        {/* Tag and Price Header */}
                                        <div className="flex items-center justify-between text-xs font-mono pt-1">
                                            <span className="px-2 py-0.5 rounded bg-stone-100 text-stone-700 font-bold uppercase text-[10px]">
                                                {b.volumeCode} • {t(`landing.catalog.curriculum_tag_${b.id}`)}
                                            </span>
                                            <span className="font-extrabold text-sm text-[#111113]">
                                                {formatPrice(b.priceSingleIdr, b.priceSingleUsd)}
                                            </span>
                                        </div>

                                        <div className="space-y-1">
                                            <h3 className="text-lg font-black text-[#111113] leading-snug">
                                                {b.title}
                                            </h3>
                                            <p className="text-xs text-stone-500 leading-relaxed">
                                                {b.subtitle}
                                            </p>
                                        </div>

                                        {/* Cocok Untuk Box */}
                                        <div className="p-2.5 rounded-lg bg-[#FAF8F5] border border-[#E5E0D5] text-xs space-y-0.5">
                                            <span className="font-mono text-[10px] font-bold text-stone-400 uppercase block">
                                                {t('landing.catalog.suitable_for')}
                                            </span>
                                            <p className="text-stone-800 font-medium text-[11px] leading-snug">
                                                {b.targetRoles.join(', ')}
                                            </p>
                                        </div>

                                        {/* Editorial CTA Narrative Paragraph */}
                                        <p className="text-xs text-stone-600 leading-relaxed font-normal pt-1">
                                            {t(`landing.catalog.cta_paragraphs.${b.id}`)}
                                        </p>
                                    </div>

                                    {/* Single Action Button: Mulai Membaca */}
                                    <div className="pt-4 border-t border-[#E5E0D5]">
                                        <a
                                            href={`/read/${b.id}/${locale}/ch-0`}
                                            className="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#111113] text-[#FAF8F5] font-bold text-xs sm:text-sm hover:bg-stone-800 transition-colors shadow-2xs min-h-[44px]"
                                        >
                                            <BookOpen className="w-4 h-4" />
                                            <span>{t('landing.catalog.start_reading')}</span>
                                        </a>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* 4. PRICING TIERS & BUNDLE (Image 4) */}
            <section id="harga" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FFFFFF]">
                <div className="w-full max-w-6xl mx-auto space-y-12">
                    <div className="text-center space-y-2">
                        <span className="inline-flex items-center gap-1.5 font-mono text-xs font-bold uppercase tracking-wider text-amber-800 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                            <span>⚡</span>
                            <span>{t('landing.pricing.badge')}</span>
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.pricing.title')}
                        </h2>
                        <p className="text-sm text-stone-600 max-w-2xl mx-auto leading-relaxed">
                            {t('landing.pricing.desc')}
                        </p>
                    </div>

                    {/* Featured Complete Trilogy Box Set Card (Image 4 top box) */}
                    {(() => {
                        const bundleTier = pricingTiers.find((t) => t.isBestValue) || pricingTiers[pricingTiers.length - 1];
                        if (!bundleTier) return null;

                        return (
                            <div className="p-6 sm:p-8 rounded-2xl border-2 border-[#111113] bg-[#FFFFFF] shadow-lg flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                                <div className="space-y-4 max-w-2xl">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="inline-block px-2.5 py-0.5 rounded text-[11px] font-mono font-extrabold uppercase bg-[#1D4ED8] text-white">
                                            {t('landing.pricing.bundle_badge_1')}
                                        </span>
                                        <span className="inline-block px-2.5 py-0.5 rounded text-[11px] font-mono font-extrabold uppercase bg-stone-100 text-stone-700 border border-stone-200">
                                            {t('landing.pricing.bundle_badge_2')}
                                        </span>
                                    </div>

                                    <div className="space-y-1">
                                        <h3 className="text-2xl sm:text-3xl font-black text-[#111113]">
                                            {t('landing.pricing.bundle_title')}
                                        </h3>
                                        <p className="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                            {t('landing.pricing.bundle_desc')}
                                        </p>
                                    </div>

                                    {/* Checklist Grid */}
                                    <ul className="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 text-xs font-medium text-stone-800">
                                        {bundleTier.features.map((feat, fIdx) => (
                                            <li key={fIdx} className="flex items-start gap-2">
                                                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                                <span>{feat}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>

                                {/* Right Side Price and CTA */}
                                <div className="w-full lg:w-72 p-6 rounded-xl border border-[#E5E0D5] bg-[#FAF8F5] flex flex-col justify-between space-y-4 shrink-0 text-center sm:text-left">
                                    <div className="space-y-1">
                                        <div className="flex items-center justify-center sm:justify-start gap-2">
                                            {currency === 'IDR' ? (
                                                <span className="text-xs line-through text-stone-400 font-mono">
                                                    Rp 197.000
                                                </span>
                                            ) : (
                                                <span className="text-xs line-through text-stone-400 font-mono">
                                                    $14.99
                                                </span>
                                            )}
                                            <span className="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                {currency === 'IDR' ? t('landing.pricing.save_badge', { amount: 'Rp 48.000' }) : t('landing.pricing.save_badge', { amount: '$5.00' })}
                                            </span>
                                        </div>

                                        <div className="text-3xl font-black text-[#111113]">
                                            {formatPrice(bundleTier.priceIdr, bundleTier.priceUsd)}
                                        </div>
                                        <p className="text-[11px] font-mono text-stone-500">
                                            {t('landing.pricing.bundle_price_sub')}
                                        </p>
                                    </div>

                                    <a
                                        href={`/checkout/${bundleTier.id}?locale=${locale}`}
                                        className="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#111113] text-white font-bold text-sm hover:bg-stone-800 transition-colors shadow-sm min-h-[44px]"
                                    >
                                        <span>{t('landing.pricing.bundle_cta', { price: formatPrice(bundleTier.priceIdr, bundleTier.priceUsd) })}</span>
                                    </a>
                                </div>
                            </div>
                        );
                    })()}

                    {/* 3 Individual Cards Below */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                        {books.map((b) => {
                            const tier = pricingTiers.find((t) => t.id === `tier-${b.id}`);
                            if (!tier) return null;

                            return (
                                <div
                                    key={b.id}
                                    className="p-6 rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] flex flex-col justify-between space-y-6 shadow-2xs hover:border-stone-400 transition-all"
                                >
                                    <div className="space-y-4">
                                        <span className="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono uppercase bg-stone-100 text-stone-700 border border-stone-200">
                                            {b.volumeCode} • {t(`landing.catalog.curriculum_tag_${b.id}`)}
                                        </span>

                                        <div className="space-y-1">
                                            <h4 className="font-extrabold text-base text-[#111113]">
                                                {b.title}
                                            </h4>
                                            <p className="text-xs text-stone-500 leading-snug">
                                                {b.subtitle}
                                            </p>
                                        </div>

                                        <div className="py-2 border-y border-[#E5E0D5]">
                                            <span className="text-2xl font-black text-[#111113]">
                                                {formatPrice(tier.priceIdr, tier.priceUsd)}
                                            </span>
                                            <span className="text-xs font-mono text-stone-400 ml-1">
                                                {t('landing.pricing.per_once')}
                                            </span>
                                        </div>

                                        <ul className="space-y-2 text-xs text-stone-700">
                                            {tier.features.slice(0, 2).map((feat, fIdx) => (
                                                <li key={fIdx} className="flex items-start gap-2">
                                                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                                    <span className="leading-snug">{feat}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>

                                    <a
                                        href={`/checkout/${tier.id}?locale=${locale}`}
                                        className="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#E5E0D5] bg-[#FAF8F5] text-stone-800 font-bold text-xs hover:bg-stone-100 transition-colors min-h-[42px]"
                                    >
                                        <span>{t('landing.pricing.single_buy_cta', { price: formatPrice(tier.priceIdr, tier.priceUsd) })}</span>
                                    </a>
                                </div>
                            );
                        })}
                    </div>

                    {/* Comparison Matrix Table (Image 4 bottom table) */}
                    <div className="space-y-4 pt-6 border-t border-[#E5E0D5]">
                        <div className="text-center">
                            <h3 className="font-bold text-base sm:text-lg text-[#111113]">
                                {t('landing.pricing.table_title')}
                            </h3>
                        </div>

                        <div className="w-full overflow-x-auto rounded-xl border border-[#E5E0D5] bg-white shadow-2xs">
                            <table className="w-full text-left text-xs">
                                <thead>
                                    <tr className="border-b border-[#E5E0D5] bg-[#FAF8F5] text-stone-600 font-mono text-[11px]">
                                        <th className="p-3.5 w-1/5">{t('landing.pricing.table_header_feature')}</th>
                                        <th className="p-3.5 w-1/5 text-center">{t('landing.pricing.table_col_b1')}</th>
                                        <th className="p-3.5 w-1/5 text-center">{t('landing.pricing.table_col_b2')}</th>
                                        <th className="p-3.5 w-1/5 text-center">{t('landing.pricing.table_col_b3')}</th>
                                        <th className="p-3.5 w-1/5 text-center bg-amber-50/70 border-x-2 border-t-2 border-amber-400 font-extrabold text-[#111113]">
                                            {t('landing.pricing.table_col_bundle')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-[#E5E0D5] text-stone-700">
                                    <tr>
                                        <td className="p-3.5 font-bold text-stone-900 bg-[#FAF8F5]/40">{t('landing.pricing.table_row_role')}</td>
                                        <td className="p-3.5 text-center">Leader Baru & Supervisor</td>
                                        <td className="p-3.5 text-center">Ketahanan & Anti-Burnout</td>
                                        <td className="p-3.5 text-center">Manajer Divisi & Lintas Tim</td>
                                        <td className="p-3.5 text-center bg-amber-50/40 border-x-2 border-amber-400 font-bold text-[#111113]">
                                            Seluruh Level Kepemimpinan
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="p-3.5 font-bold text-stone-900 bg-[#FAF8F5]/40">{t('landing.pricing.table_row_chapters')}</td>
                                        <td className="p-3.5 text-center">15 Bab + 3 File Master PDF</td>
                                        <td className="p-3.5 text-center">15 Bab + 3 File Master PDF</td>
                                        <td className="p-3.5 text-center">15 Bab + 3 File Master PDF</td>
                                        <td className="p-3.5 text-center bg-amber-50/40 border-x-2 border-amber-400 font-bold text-[#111113]">
                                            45 Bab Penuh + 9 File Master PDF
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="p-3.5 font-bold text-stone-900 bg-[#FAF8F5]/40">{t('landing.pricing.table_row_frameworks')}</td>
                                        <td className="p-3.5 text-center">Rumus Target 3K & Koreksi 4 Mata</td>
                                        <td className="p-3.5 text-center">Perisai Kaca & Detachment 18:00</td>
                                        <td className="p-3.5 text-center">Coaching the Coach & Rumus 3S</td>
                                        <td className="p-3.5 text-center bg-amber-50/40 border-x-2 border-amber-400 font-bold text-[#111113]">
                                            Seluruh Framework (CCL, Harvard, Gallup)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="p-3.5 font-bold text-stone-900 bg-[#FAF8F5]/40">{t('landing.pricing.table_row_price')}</td>
                                        <td className="p-3.5 text-center font-bold text-stone-900">{formatPrice(49000, 3.99)}</td>
                                        <td className="p-3.5 text-center font-bold text-stone-900">{formatPrice(49000, 3.99)}</td>
                                        <td className="p-3.5 text-center font-bold text-stone-900">{formatPrice(99000, 6.99)}</td>
                                        <td className="p-3.5 text-center bg-amber-50/40 border-x-2 border-b-2 border-amber-400 font-black text-sm text-[#111113]">
                                            {formatPrice(149000, 9.99)} <span className="text-[10px] font-normal text-emerald-700 block">(Hemat 24%)</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            {/* 5. FAQ TAKTIS ACCORDION */}
            <section className="w-full py-16 px-4 sm:px-6 lg:px-10 bg-[#FAF8F5]">
                <div className="w-full max-w-3xl mx-auto space-y-8">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-stone-500">
                            {t('landing.faq.badge')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.faq.title')}
                        </h2>
                    </div>

                    <div className="space-y-3">
                        {faqItems.map((item, idx) => {
                            const isExpanded = expandedFaq === idx;
                            return (
                                <div
                                    key={idx}
                                    className="border border-[#E5E0D5] rounded-xl overflow-hidden bg-[#FFFFFF] transition-colors"
                                >
                                    <button
                                        type="button"
                                        onClick={() => setExpandedFaq(isExpanded ? null : idx)}
                                        aria-expanded={isExpanded}
                                        className="w-full text-left p-4 sm:p-5 flex items-center justify-between gap-4 font-bold text-sm sm:text-base text-[#111113] hover:text-[#1D4ED8] transition-colors"
                                    >
                                        <span className="flex items-center gap-2">
                                            <HelpCircle className="w-4 h-4 text-stone-400 shrink-0" />
                                            <span>{item.q}</span>
                                        </span>
                                        <ChevronDown className={`w-4 h-4 text-stone-400 shrink-0 transition-transform ${isExpanded ? 'rotate-180' : ''}`} />
                                    </button>

                                    {isExpanded && (
                                        <div className="px-5 pb-5 pt-1 text-xs sm:text-sm text-stone-600 leading-relaxed border-t border-[#E5E0D5]/60 bg-[#FAF8F5]">
                                            {item.a}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>
        </MarketingFullWidthLayout>
    );
}
