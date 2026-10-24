import React, { useEffect, useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import MarketingFullWidthLayout from '@/Layouts/MarketingFullWidthLayout';
import { InContentRegisterToggle } from '@/Components/navigation/InContentRegisterToggle';
import { SupportedLocaleItem } from '@/Components/navigation/GlobalLocaleMenu';
import { HeroTrilogy16x9Comp } from '@/Components/remotion/compositions/HeroTrilogy16x9Comp';
import { BookId, ContentRegister, LocaleCode } from '@/types/remotion';
import { Player, PlayerRef } from '@remotion/player';
import { useTranslation } from '@/hooks/useTranslation';
import {
    ArrowRight,
    BookOpen,
    Check,
    ChevronDown,
    Compass,
    Copy,
    HelpCircle,
    RotateCcw,
    ShieldCheck,
    Sparkles,
    User,
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
    register: ContentRegister;
    supportedLocales: SupportedLocaleItem[];
    books: BookItem[];
    pricingTiers: PricingTierItem[];
    user: { name: string; email: string; role?: string } | null;
}

export default function Landing({
    locale,
    register: initialRegister,
    supportedLocales,
    books,
    pricingTiers,
    user,
}: LandingProps) {
    const { t, currency, formatPrice } = useTranslation();
    const [register, setRegister] = useState<ContentRegister>(initialRegister);
    const [activeVolume, setActiveVolume] = useState<BookId>('book-1');
    const [demoTeammate, setDemoTeammate] = useState('Budi');
    const [demoCopied, setDemoCopied] = useState(false);
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

    const handleRegisterToggle = (newRegister: ContentRegister) => {
        setRegister(newRegister);
        const url = new URL(window.location.href);
        url.searchParams.set('reg', newRegister);
        router.visit(url.pathname + url.search, {
            preserveScroll: true,
            preserveState: true,
        });
    };

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

    const heroTitle = locale === 'id'
        ? (register === 'casual' ? t('landing.hero.title_casual') : t('landing.hero.title_standard'))
        : t('landing.hero.title');

    const heroHighlight = locale === 'id'
        ? (register === 'casual' ? t('landing.hero.title_casual_highlight') : t('landing.hero.title_standard_highlight'))
        : t('landing.hero.title_highlight');

    const heroDesc = locale === 'id'
        ? (register === 'casual' ? t('landing.hero.desc_casual') : t('landing.hero.desc_standard'))
        : t('landing.hero.desc');

    const copyDemoScript = () => {
        const text = locale === 'id'
            ? (register === 'casual'
                ? t('landing.demo.sample_script_casual', { name: demoTeammate.trim() || 'Budi' })
                : t('landing.demo.sample_script_standard', { name: demoTeammate.trim() || 'Budi' }))
            : t('landing.demo.sample_script', { name: demoTeammate.trim() || 'Budi' });

        navigator.clipboard.writeText(text).then(() => {
            setDemoCopied(true);
            setTimeout(() => setDemoCopied(false), 2000);
        });
    };

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
                    {/* Left Column: Value Prop & In-Content Toggle */}
                    <div className="flex-1 space-y-6">
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-[#E5E0D5] bg-[#FFFFFF] text-xs font-mono font-bold text-stone-700 shadow-2xs">
                                <span className="w-2 h-2 rounded-full bg-[#1D4ED8]" />
                                <span>{t('landing.badge')}</span>
                            </div>

                            {/* In-Content Register Toggle (Strictly ID only) */}
                            <InContentRegisterToggle
                                locale={locale}
                                register={register}
                                onRegisterChange={handleRegisterToggle}
                                size="sm"
                            />
                        </div>

                        <h1 className="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-[#111113] leading-[1.12]">
                            {heroTitle}
                            <span className="block text-[#1D4ED8] mt-2">{heroHighlight}</span>
                        </h1>

                        <p className="text-base sm:text-lg text-stone-600 max-w-xl leading-relaxed">
                            {heroDesc}
                        </p>

                        <div className="flex flex-wrap items-center gap-4 pt-2">
                            <a
                                href={`/read/book-1/${locale}/ch-0${locale === 'id' ? `?reg=${register}` : ''}`}
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
                            <span className="text-stone-400 hidden sm:inline">Pratinjau Animasi:</span>
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
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-[#1D4ED8] bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                            {t('landing.quiz.header_badge')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.quiz.title')}
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            {t('landing.quiz.desc')}
                        </p>
                    </div>

                    <div className="p-6 sm:p-8 rounded-2xl border border-[#E5E0D5] bg-[#FAF8F5] shadow-sm space-y-6">
                        {/* Progress Indicator */}
                        <div className="flex items-center gap-2">
                            {[1, 2, 3].map((step) => (
                                <div
                                    key={step}
                                    className={`h-2 flex-1 rounded-full transition-colors ${
                                        quizCurrentStep >= step ? 'bg-[#1D4ED8]' : 'bg-[#E5E0D5]'
                                    }`}
                                />
                            ))}
                        </div>

                        {quizCurrentStep === 1 && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between text-xs font-mono text-stone-500">
                                    <span>{t('landing.quiz.step_1_meta')}</span>
                                    <span>{t('landing.quiz.step_1_cat')}</span>
                                </div>
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
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#1D4ED8] hover:bg-blue-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_1_o1')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 1: 'book-2' }));
                                            setQuizCurrentStep(2);
                                        }}
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#EA580C] hover:bg-orange-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_1_o2')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 1: 'book-3' }));
                                            setQuizCurrentStep(2);
                                        }}
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#0F766E] hover:bg-teal-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_1_o3')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 2 && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between text-xs font-mono text-stone-500">
                                    <span>{t('landing.quiz.step_2_meta')}</span>
                                    <span>{t('landing.quiz.step_2_cat')}</span>
                                </div>
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
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#1D4ED8] hover:bg-blue-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_2_o1')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 2: 'book-2' }));
                                            setQuizCurrentStep(3);
                                        }}
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#EA580C] hover:bg-orange-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_2_o2')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 2: 'book-3' }));
                                            setQuizCurrentStep(3);
                                        }}
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#0F766E] hover:bg-teal-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_2_o3')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 3 && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between text-xs font-mono text-stone-500">
                                    <span>{t('landing.quiz.step_3_meta')}</span>
                                    <span>{t('landing.quiz.step_3_cat')}</span>
                                </div>
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
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#1D4ED8] hover:bg-blue-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_3_o1')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 3: 'book-2' }));
                                            setQuizCurrentStep(4);
                                        }}
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#EA580C] hover:bg-orange-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_3_o2')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQuizAnswers((prev) => ({ ...prev, 3: 'book-3' }));
                                            setQuizCurrentStep(4);
                                        }}
                                        className="w-full text-left p-4 rounded-xl border border-[#E5E0D5] bg-white hover:border-[#0F766E] hover:bg-teal-50/40 transition-all font-medium text-xs sm:text-sm text-stone-800 min-h-[44px] flex items-center justify-between"
                                    >
                                        <span>{t('landing.quiz.step_3_o3')}</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
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
                                        Kerangka: {recommendedBook.keyFrameworks.join(' • ')}
                                    </span>
                                </div>

                                <div className="pt-2 flex flex-wrap gap-3">
                                    <a
                                        href={`/read/${recommendedBook.id}/${locale}/ch-0${locale === 'id' ? `?reg=${register}` : ''}`}
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

            {/* 3. LIVE SCRIPT PERSONALIZER DEMO */}
            <section id="demo-skrip" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FAF8F5]">
                <div className="w-full max-w-4xl mx-auto space-y-8">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-100 px-3 py-1 rounded-full border border-amber-300">
                            {t('landing.demo.badge')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.demo.title')}
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            {t('landing.demo.desc')}
                        </p>
                    </div>

                    <div className="p-6 sm:p-8 rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-sm space-y-6">
                        {/* Simulation Controls */}
                        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-[#E5E0D5]">
                            <div className="w-full sm:w-72 space-y-1.5">
                                <label
                                    htmlFor="demo-name-input"
                                    className="flex items-center gap-1.5 text-xs font-mono font-bold uppercase text-stone-700"
                                >
                                    <User className="w-3.5 h-3.5 text-[#1D4ED8]" />
                                    <span>{t('landing.demo.teammate_label')}:</span>
                                </label>
                                <input
                                    id="demo-name-input"
                                    type="text"
                                    value={demoTeammate}
                                    onChange={(e) => setDemoTeammate(e.target.value)}
                                    placeholder="Contoh: Rina, Budi, Dimas"
                                    className="w-full px-3 py-2 text-sm rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8]"
                                />
                            </div>

                            {/* In-Content Toggle for Demo (ID only) */}
                            {locale === 'id' && (
                                <div className="space-y-1.5 self-stretch sm:self-auto">
                                    <span className="text-xs font-mono font-bold uppercase text-stone-500 block">
                                        Pilihan Gaya Bahasa:
                                    </span>
                                    <InContentRegisterToggle
                                        locale={locale}
                                        register={register}
                                        onRegisterChange={setRegister}
                                    />
                                </div>
                            )}
                        </div>

                        {/* Live Script Bubble */}
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <span className="font-mono text-xs font-bold text-stone-500 flex items-center gap-1.5">
                                    <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                                    <span>CONTEKAN DIALOG OBROLAN 1 (RUMUS 3K)</span>
                                </span>
                                <button
                                    type="button"
                                    onClick={copyDemoScript}
                                    className="inline-flex items-center gap-1 text-xs font-mono font-semibold px-3 py-1 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] text-stone-700 hover:text-[#111113] hover:bg-stone-100 transition-colors"
                                >
                                    {demoCopied ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5" />}
                                    <span>{demoCopied ? t('landing.demo.copied') : t('landing.demo.copy_script')}</span>
                                </button>
                            </div>

                            <div className="p-5 rounded-xl bg-[#FAF8F5] border-l-4 border-[#1D4ED8] font-sans text-sm sm:text-base leading-relaxed text-[#111113] space-y-2">
                                <p>
                                    {locale === 'id' ? (
                                        register === 'casual'
                                            ? t('landing.demo.sample_script_casual', { name: demoTeammate.trim() || 'Budi' })
                                            : t('landing.demo.sample_script_standard', { name: demoTeammate.trim() || 'Budi' })
                                    ) : (
                                        t('landing.demo.sample_script', { name: demoTeammate.trim() || 'Budi' })
                                    )}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* 4. KATALOG 3 VOLUME PLAYBOOK */}
            <section id="katalog" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FFFFFF]">
                <div className="w-full max-w-6xl mx-auto space-y-10">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-stone-500">
                            TRILOGI TAKTIS KEPEMIMPINAN
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            Tiga Volume Mandiri untuk Setiap Fase Karier
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            Dirancang agar dapat dibaca terpisah sesuai friksi paling mendesak di tim Anda saat ini.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {books.map((b) => (
                            <div
                                key={b.id}
                                className="rounded-2xl border border-[#E5E0D5] bg-[#FAF8F5] p-6 flex flex-col justify-between space-y-6 hover:border-stone-400 transition-all shadow-2xs"
                            >
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <span
                                            className="px-2.5 py-1 rounded text-xs font-mono font-bold text-white uppercase"
                                            style={{ backgroundColor: b.accentHex }}
                                        >
                                            {b.volumeCode}
                                        </span>
                                        <span className="text-xs font-mono text-stone-500">15 Bab</span>
                                    </div>

                                    <div className="space-y-1.5">
                                        <h3 className="text-lg font-black text-[#111113] leading-snug">
                                            {b.title}
                                        </h3>
                                        <p className="text-xs text-stone-600 leading-relaxed">
                                            {b.subtitle}
                                        </p>
                                    </div>

                                    <div className="pt-2 border-t border-[#E5E0D5] space-y-2">
                                        <span className="text-[11px] font-mono font-bold uppercase text-stone-400 block">
                                            Cocok Untuk:
                                        </span>
                                        <div className="flex flex-wrap gap-1.5">
                                            {b.targetRoles.map((role, rIdx) => (
                                                <span
                                                    key={rIdx}
                                                    className="px-2 py-0.5 rounded text-[11px] bg-white border border-[#E5E0D5] text-stone-700 font-medium"
                                                >
                                                    {role}
                                                </span>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="space-y-1">
                                        <span className="text-[11px] font-mono font-bold uppercase text-stone-400 block">
                                            Kerangka Kunci:
                                        </span>
                                        <p className="text-xs font-mono text-stone-700">
                                            {b.keyFrameworks.join(' • ')}
                                        </p>
                                    </div>
                                </div>

                                <div className="pt-4 border-t border-[#E5E0D5] flex items-center justify-between">
                                    <div className="space-y-0.5">
                                        <span className="text-[10px] font-mono text-stone-400 uppercase block">Akses Tunggal:</span>
                                        <span className="text-base font-extrabold text-[#111113]">
                                            {formatPrice(b.priceSingleIdr, b.priceSingleUsd)}
                                        </span>
                                    </div>

                                    <a
                                        href={`/read/${b.id}/${locale}/ch-0${locale === 'id' ? `?reg=${register}` : ''}`}
                                        className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#111113] text-white text-xs font-bold hover:bg-stone-800 transition-colors"
                                    >
                                        <BookOpen className="w-3.5 h-3.5" />
                                        <span>{t('landing.hero.cta_read_free')}</span>
                                    </a>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* 5. PRICING TIERS & BUNDLE */}
            <section id="harga" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FAF8F5]">
                <div className="w-full max-w-6xl mx-auto space-y-10">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-[#1D4ED8] bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                            {t('landing.pricing.badge')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            {t('landing.pricing.title')}
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            {t('landing.pricing.desc')}
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                        {pricingTiers.map((tier) => {
                            const isBundle = tier.isBestValue;
                            return (
                                <div
                                    key={tier.id}
                                    className={`rounded-2xl p-6 flex flex-col justify-between space-y-6 transition-all ${
                                        isBundle
                                            ? 'border-2 border-[#111113] bg-[#FFFFFF] shadow-lg lg:-translate-y-2'
                                            : 'border border-[#E5E0D5] bg-[#FFFFFF] shadow-2xs'
                                    }`}
                                >
                                    <div className="space-y-4">
                                        {isBundle ? (
                                            <span className="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-extrabold uppercase bg-[#FFE66D] text-[#111113] border border-[#111113]">
                                                {t('landing.pricing.best_value_badge')}
                                            </span>
                                        ) : (
                                            <span className="inline-block px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-stone-100 text-stone-600">
                                                {t('landing.pricing.single_badge')}
                                            </span>
                                        )}

                                        <div className="space-y-1">
                                            <h3 className="font-extrabold text-base text-[#111113]">
                                                {tier.name}
                                            </h3>
                                            <p className="text-xs text-stone-500 leading-snug">
                                                {tier.description}
                                            </p>
                                        </div>

                                        <div className="py-2">
                                            {currency === 'IDR' && tier.strikePriceIdr && (
                                                <span className="text-xs line-through text-stone-400 block font-mono">
                                                    Rp{tier.strikePriceIdr.toLocaleString('id-ID')}
                                                </span>
                                            )}
                                            {currency === 'USD' && isBundle && (
                                                <span className="text-xs line-through text-stone-400 block font-mono">
                                                    $29.99
                                                </span>
                                            )}
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-2xl font-black text-[#111113]">
                                                    {formatPrice(tier.priceIdr, tier.priceUsd)}
                                                </span>
                                                <span className="text-[11px] font-mono text-stone-400">{t('landing.pricing.per_lifetime')}</span>
                                            </div>
                                        </div>

                                        <ul className="space-y-2 text-xs text-stone-700 border-t border-[#E5E0D5] pt-4">
                                            {tier.features.map((feat, fIdx) => (
                                                <li key={fIdx} className="flex items-start gap-2">
                                                    <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                                    <span className="leading-tight">{feat}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>

                                    <div className="pt-4 border-t border-[#E5E0D5]">
                                        <a
                                            href={`/checkout/${tier.id}?locale=${locale}`}
                                            className={`w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl font-bold text-xs sm:text-sm transition-colors min-h-[44px] ${
                                                isBundle
                                                    ? 'bg-[#111113] text-white hover:bg-stone-800'
                                                    : 'border border-[#E5E0D5] bg-[#FAF8F5] text-stone-800 hover:bg-stone-100'
                                            }`}
                                        >
                                            <ShieldCheck className="w-4 h-4" />
                                            <span>{t('landing.pricing.cta_buy')}</span>
                                        </a>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* 6. FAQ TAKTIS ACCORDION */}
            <section className="w-full py-16 px-4 sm:px-6 lg:px-10 bg-[#FFFFFF]">
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
                                    className="border border-[#E5E0D5] rounded-xl overflow-hidden bg-[#FAF8F5] transition-colors"
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
                                        <div className="px-5 pb-5 pt-1 text-xs sm:text-sm text-stone-600 leading-relaxed border-t border-[#E5E0D5]/60 bg-white">
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
