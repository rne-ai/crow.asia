import React, { useEffect, useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import MarketingFullWidthLayout from '@/Layouts/MarketingFullWidthLayout';
import { InContentRegisterToggle } from '@/Components/navigation/InContentRegisterToggle';
import { SupportedLocaleItem } from '@/Components/navigation/GlobalLocaleMenu';
import { HeroTrilogy16x9Comp } from '@/Components/remotion/compositions/HeroTrilogy16x9Comp';
import { Player, PlayerRef } from '@remotion/player';
import { BookId, ContentRegister, LocaleCode } from '@/types/remotion';
import {
    ArrowRight,
    BookOpen,
    Check,
    CheckCircle2,
    ChevronDown,
    Compass,
    Copy,
    HelpCircle,
    RotateCcw,
    Shield,
    ShieldCheck,
    Sparkles,
    User,
    Zap
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

    const copyDemoScript = () => {
        const text =
            register === 'casual'
                ? `Halo ${demoTeammate}, buat tugas laporan mingguan ini, yuk kita sepakatin dulu Rumus 3K-nya biar satu frekuensi:\n1. Keluaran: Dokumen analisis performa tim 3 halaman.\n2. Kriteria: Metrik akurasi 98% dan rekomendasi perbaikan terurai.\n3. Kalender: Draf pertama masuk hari Kamis jam 15.00 WIB.\nMenurut pertimbangan lo, ada kendala yang butuh kita bedah bareng?`
                : `Halo ${demoTeammate}, untuk penugasan laporan mingguan ini, mari kita sepakati terlebih dahulu Formula 3K agar ekspektasi selaras sejak awal:\n1. Keluaran: Dokumen evaluasi kinerja tim setebal 3 halaman.\n2. Kriteria: Tingkat akurasi minimal 98% disertai rekomendasi perbaikan terstruktur.\n3. Kalender: Draf pertama diserahkan hari Kamis pukul 15.00 WIB.\nMenurut pandangan Anda, apakah ada bagian yang memerlukan dukungan teknis dari saya?`;

        navigator.clipboard.writeText(text).then(() => {
            setDemoCopied(true);
            setTimeout(() => setDemoCopied(false), 2000);
        });
    };

    const faqItems = [
        {
            q: 'Apakah formatnya buku fisik yang dikirim atau web digital?',
            a: 'crow.asia adalah field playbook interaktif web-first berkecepatan tinggi (< 25ms). Anda dapat membacanya langsung di browser HP maupun laptop tanpa perlu instal aplikasi, lengkap dengan animasi interaktif Remotion 16:9, checklist cloud, dan file Master PDF A5 siap unduh atau cetak mandiri.',
        },
        {
            q: 'Apa perbedaan versi Kasual (Gue/Lo) dan Standar (Saya/Kamu)?',
            a: 'Substansi strategi, rumus kepemimpinan, dan studi kasusnya 100% identik. Perbedaannya hanya pada ragam bahasa komunikasi agar pas dengan kultur kantor Anda: gaya Kasual memakai sapaan Gue/Lo yang akrab, sedangkan Standar memakai Saya/Kamu yang formal dan santun.',
        },
        {
            q: 'Berapa lama masa berlaku akses setelah pembayaran?',
            a: 'Akses berlaku seumur hidup (lifetime access) untuk akun Anda. Seluruh pembaruan naskah di masa depan dan sinkronisasi checklist progres dapat terus diakses tanpa biaya langganan bulanan.',
        },
        {
            q: 'Metode pembayaran apa saja yang diterima?',
            a: 'Kami menggunakan Mayar.id dengan dukungan QRIS instan (GoPay, OVO, Dana, ShopeePay), Transfer Bank / Virtual Account (BCA, Mandiri, BRI, BNI, Permata), dan Kartu Kredit dengan aktivasi akses instan otomatis.',
        },
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
                                <span>TACTICAL FIELD PLAYBOOK 2026</span>
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
                            {register === 'casual' ? (
                                <>
                                    Baru Jadi Leader atau Mulai Kewalahan Ngurusin Tim?
                                    <span className="block text-[#1D4ED8] mt-2">Ini Contekan Meja Kerja Lo.</span>
                                </>
                            ) : (
                                <>
                                    Baru Diangkat Menjadi Pemimpin atau Menghadapi Beban Tim?
                                    <span className="block text-[#1D4ED8] mt-2">Panduan Meja Kerja Taktis Anda.</span>
                                </>
                            )}
                        </h1>

                        <p className="text-base sm:text-lg text-stone-600 max-w-xl leading-relaxed">
                            {register === 'casual'
                                ? 'Bukan teori motivasi bertele-tele. Ini playbook 1-on-1 konkret, rumus kalibrasi tugas (L1–L4), dan skrip siap salin buat bikin tim mandiri tanpa lo harus burnout.'
                                : 'Bukan sekadar wacana motivasi kepemimpinan. Ini buku pedoman 1-on-1 taktis, formula kalibrasi tugas (L1–L4), dan skrip teruji untuk membangun tim mandiri secara berkelanjutan.'}
                        </p>

                        <div className="flex flex-wrap items-center gap-4 pt-2">
                            <a
                                href={`/read/book-1/${locale}/ch-0${locale === 'id' ? `?reg=${register}` : ''}`}
                                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#111113] text-[#FAF8F5] font-bold text-sm hover:bg-stone-800 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] shadow-xs min-h-[44px]"
                            >
                                <BookOpen className="w-4 h-4" />
                                <span>Baca Gratis Bab 0 & 1</span>
                            </a>
                            <a
                                href="#harga"
                                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl border border-[#E5E0D5] bg-[#FFFFFF] text-[#111113] font-bold text-sm hover:bg-stone-50 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] shadow-2xs min-h-[44px]"
                            >
                                <span>Lihat Paket Bundel</span>
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
                                        Memuat Showcase Remotion 16:9...
                                    </div>
                                ) : (
                                    <Player
                                        ref={playerRef}
                                        component={HeroTrilogy16x9Comp}
                                        inputProps={{
                                            activeVolume,
                                            locale,
                                            register,
                                            teammateName: demoTeammate,
                                        }}
                                        durationInFrames={300}
                                        compositionWidth={1280}
                                        compositionHeight={720}
                                        fps={30}
                                        loop
                                        autoPlay
                                        initiallyMuted
                                        numberOfSharedAudioTags={0}
                                        acknowledgeRemotionLicense
                                        controls={false}
                                        style={{ width: '100%', height: '100%' }}
                                    />
                                )}
                            </div>

                            {/* Volume Selector for Remotion Showcase */}
                            <div className="mt-2.5 flex items-center justify-between gap-1.5 pt-2 border-t border-[#E5E0D5]">
                                <span className="font-mono text-[11px] font-bold uppercase text-stone-500 hidden sm:inline">
                                    Pilih Preview Playbook:
                                </span>
                                <div className="flex gap-1.5 w-full sm:w-auto">
                                    {(['book-1', 'book-2', 'book-3'] as const).map((bId) => {
                                        const isSel = activeVolume === bId;
                                        const bData = books.find((b) => b.id === bId);
                                        return (
                                            <button
                                                key={bId}
                                                type="button"
                                                onClick={() => setActiveVolume(bId)}
                                                className={`flex-1 sm:flex-initial px-3 py-1.5 rounded-lg text-xs font-mono font-bold transition-all min-h-[38px] ${
                                                    isSel
                                                        ? 'bg-[#111113] text-white shadow-xs'
                                                        : 'bg-[#FAF8F5] text-stone-600 hover:text-[#111113] border border-[#E5E0D5]'
                                                }`}
                                            >
                                                {bData?.volumeCode || bId.toUpperCase()}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* 2. KUIS DIAGNOSIS KEPEMIMPINAN 45 DETIK */}
            <section id="diagnosis" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FFFFFF]">
                <div className="w-full max-w-4xl mx-auto space-y-8">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-[#1D4ED8] bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                            DIAGNOSIS 45 DETIK
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            Situasi Mana yang Paling Menguras Energi Anda di Kantor?
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            Jawab 3 pertanyaan situasional berikut untuk menemukan Volume Playbook yang langsung menyelesaikan friksi tim Anda.
                        </p>
                    </div>

                    {/* Step Questions Card */}
                    <div className="p-6 sm:p-8 rounded-2xl border border-[#E5E0D5] bg-[#FAF8F5] space-y-6">
                        {quizCurrentStep === 1 && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between text-xs font-mono text-stone-500">
                                    <span>PERTANYAAN 01 / 03</span>
                                    <span>POLA EKSEKUSI TUGAS</span>
                                </div>
                                <h3 className="font-extrabold text-base sm:text-lg text-[#111113]">
                                    Bagaimana ritme kerja harian antara Anda dan anggota tim saat ini?
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
                                        <span>• "Anggota tim sering nunggu instruksi detail, bolak-balik nanya hal yang sama, dan kurang inisiatif."</span>
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
                                        <span>• "Beban kerja tim menumpuk tidak teratur, jam kerja molor, dan saya kehabisan energi menjaga ritme."</span>
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
                                        <span>• "Saya memimpin beberapa manajer / team lead, tapi masing-masing tim jalan sendiri tanpa keselarasan sasaran."</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 2 && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between text-xs font-mono text-stone-500">
                                    <span>PERTANYAAN 02 / 03</span>
                                    <span>TITIK HAMBATAN KOMUNIKASI</span>
                                </div>
                                <h3 className="font-extrabold text-base sm:text-lg text-[#111113]">
                                    Percakapan apa yang paling canggung atau sering Anda hindari di kantor?
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
                                        <span>• "Memberikan teguran atau koreksi saat kerjaan melenceng tanpa merusak mental staf."</span>
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
                                        <span>• "Menetapkan batasan beban kerja (Boundary Shield) dan menolak interupsi tak terencana dari atasan."</span>
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
                                        <span>• "Mendelegasikan kepemilikan keputusan strategis ke manajer di bawah saya tanpa micromanagement."</span>
                                        <ArrowRight className="w-4 h-4 text-stone-400 shrink-0 ml-2" />
                                    </button>
                                </div>
                            </div>
                        )}

                        {quizCurrentStep === 3 && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between text-xs font-mono text-stone-500">
                                    <span>PERTANYAAN 03 / 03</span>
                                    <span>HASIL TARGET 30 HARI</span>
                                </div>
                                <h3 className="font-extrabold text-base sm:text-lg text-[#111113]">
                                    Perubahan paling krusial apa yang ingin Anda wujudkan dalam 1 bulan ke depan?
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
                                        <span>• "Tim bisa menyepakati target Rumus 3K di awal dan beroperasi mandiri sesuai kalibrasi L1–L4."</span>
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
                                        <span>• "Menerapkan formula beban 30/70, memangkas rapat mubazir, dan mengembalikan energi tim."</span>
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
                                        <span>• "Membangun sistem kaderisasi manajer baru dan menyelaraskan eksekutif lintas fungsi."</span>
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
                                        REKOMENDASI PLAYBOOK ANDA: {recommendedBook.volumeCode}
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
                                        <span>Ulangi Kuis</span>
                                    </button>
                                </div>

                                <div className="space-y-2">
                                    <h3 className="text-xl sm:text-2xl font-black text-[#111113]">
                                        {recommendedBook.title}
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
                                        <span>Buka Bab 0 & 1 Gratis</span>
                                    </a>
                                    <a
                                        href="#harga"
                                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] text-stone-800 text-xs sm:text-sm font-bold hover:bg-stone-100 transition-colors"
                                    >
                                        <span>Dapatkan Playbook Ini (Rp149.000)</span>
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
                            LIVE DEMO SKRIP 1-ON-1
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            Ketik Nama Staf Anda & Lihat Skrip Beradaptasi Seketika
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            Semua dialog empat mata di dalam 45 bab otomatis terpersonalisi tanpa perlu mengedit naskah secara manual.
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
                                    <span>Nama Anggota Tim:</span>
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

                            {/* In-Content Toggle for Demo */}
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
                                    <span>{demoCopied ? 'Tersalin!' : 'Salin Skrip'}</span>
                                </button>
                            </div>

                            <div className="p-5 rounded-xl bg-[#FAF8F5] border-l-4 border-[#1D4ED8] font-sans text-sm sm:text-base leading-relaxed text-[#111113] space-y-2">
                                {register === 'casual' ? (
                                    <>
                                        <p>
                                            "Halo <span className="teammate-token">{demoTeammate.trim() || 'Budi'}</span>, buat tugas laporan mingguan ini, yuk kita sepakatin dulu Rumus 3K-nya biar satu frekuensi sejak awal:"
                                        </p>
                                        <ol className="list-decimal pl-5 space-y-1 text-sm text-stone-700">
                                            <li><strong>Keluaran:</strong> Dokumen analisis performa tim 3 halaman.</li>
                                            <li><strong>Kriteria:</strong> Metrik akurasi 98% dan rekomendasi perbaikan terurai.</li>
                                            <li><strong>Kalender:</strong> Draf pertama masuk hari Kamis jam 15.00 WIB.</li>
                                        </ol>
                                        <p className="text-sm italic text-stone-600">
                                            "Menurut pertimbangan lo, ada bagian yang perlu kita sesuaikan dulu atau ada kendala teknis yang butuh dukungan dari gue?"
                                        </p>
                                    </>
                                ) : (
                                    <>
                                        <p>
                                            "Halo <span className="teammate-token">{demoTeammate.trim() || 'Budi'}</span>, untuk penugasan laporan mingguan ini, mari kita sepakati terlebih dahulu Formula 3K agar ekspektasi selaras sejak awal:"
                                        </p>
                                        <ol className="list-decimal pl-5 space-y-1 text-sm text-stone-700">
                                            <li><strong>Keluaran:</strong> Dokumen evaluasi kinerja tim setebal 3 halaman.</li>
                                            <li><strong>Kriteria:</strong> Tingkat akurasi minimal 98% disertai rekomendasi perbaikan terstruktur.</li>
                                            <li><strong>Kalender:</strong> Draf pertama diserahkan hari Kamis pukul 15.00 WIB.</li>
                                        </ol>
                                        <p className="text-sm italic text-stone-600">
                                            "Menurut pandangan Anda, apakah ada bagian yang memerlukan penyesuaian atau membutuhkan dukungan teknis dari saya?"
                                        </p>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* 4. KATALOG 3 PLAYBOOK (VOLUME CARDS) */}
            <section id="katalog" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FFFFFF]">
                <div className="w-full max-w-7xl mx-auto space-y-10">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-stone-500">
                            TRILOGI PLAYBOOK LENGKAP
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            Dirancang untuk Tiga Jenjang Tanggung Jawab Kepemimpinan
                        </h2>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {books.map((b) => (
                            <div
                                key={b.id}
                                className="p-6 rounded-2xl border border-[#E5E0D5] bg-[#FAF8F5] flex flex-col justify-between space-y-6 shadow-2xs hover:shadow-xs transition-shadow"
                            >
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <span
                                            className="px-2.5 py-1 rounded text-xs font-mono font-bold text-white uppercase"
                                            style={{ backgroundColor: b.accentHex }}
                                        >
                                            {b.volumeCode}
                                        </span>
                                        <span className="font-mono text-xs font-bold text-stone-900">
                                            Rp{b.priceSingleIdr.toLocaleString('id-ID')}
                                        </span>
                                    </div>

                                    <div className="space-y-1">
                                        <h3 className="font-extrabold text-lg sm:text-xl text-[#111113]">
                                            {b.title}
                                        </h3>
                                        <p className="text-xs sm:text-sm text-stone-600 leading-relaxed">
                                            {b.subtitle}
                                        </p>
                                    </div>

                                    <div className="space-y-2 pt-2 border-t border-[#E5E0D5]/70">
                                        <span className="text-[11px] font-mono uppercase font-bold text-stone-400 block">
                                            Untuk Peran:
                                        </span>
                                        <div className="flex flex-wrap gap-1.5">
                                            {b.targetRoles.map((r, rIdx) => (
                                                <span
                                                    key={rIdx}
                                                    className="px-2 py-0.5 rounded text-[11px] bg-white border border-[#E5E0D5] text-stone-700 font-medium"
                                                >
                                                    {r}
                                                </span>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="space-y-2 pt-2 border-t border-[#E5E0D5]/70">
                                        <span className="text-[11px] font-mono uppercase font-bold text-stone-400 block">
                                            Kerangka Inti:
                                        </span>
                                        <ul className="text-xs text-stone-700 space-y-1">
                                            {b.keyFrameworks.map((f, fIdx) => (
                                                <li key={fIdx} className="flex items-center gap-1.5">
                                                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                                                    <span>{f}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                </div>

                                <div className="pt-4 border-t border-[#E5E0D5] flex gap-2">
                                    <a
                                        href={`/read/${b.id}/${locale}/ch-0${locale === 'id' ? `?reg=${register}` : ''}`}
                                        className="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#E5E0D5] bg-white text-xs font-bold text-[#111113] hover:bg-stone-50 transition-colors"
                                    >
                                        <BookOpen className="w-3.5 h-3.5" />
                                        <span>Baca Sampel Bab 0 & 1</span>
                                    </a>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* 5. MATRIKS HARGA (PRICING TIERS) */}
            <section id="harga" className="w-full py-16 px-4 sm:px-6 lg:px-10 border-b border-[#E5E0D5] bg-[#FAF8F5]">
                <div className="w-full max-w-6xl mx-auto space-y-10">
                    <div className="text-center space-y-2">
                        <span className="font-mono text-xs font-bold uppercase tracking-wider text-[#1D4ED8] bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                            PAKET AKSES RESMI
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            Satu Kali Beli. Akses Selamanya & Bebas Biaya Langganan.
                        </h2>
                        <p className="text-sm text-stone-600 max-w-lg mx-auto">
                            Dapatkan akses naskah penuh 15 bab, panggung animasi Remotion 16:9, simulasi skrip 1-on-1, dan master PDF A5.
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
                                                ★ PILIHAN TERBAIK (HEMAT 33%)
                                            </span>
                                        ) : (
                                            <span className="inline-block px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-stone-100 text-stone-600">
                                                SATUAN
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
                                            {tier.strikePriceIdr && (
                                                <span className="text-xs line-through text-stone-400 block font-mono">
                                                    Rp{tier.strikePriceIdr.toLocaleString('id-ID')}
                                                </span>
                                            )}
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-2xl font-black text-[#111113]">
                                                    Rp{tier.priceIdr.toLocaleString('id-ID')}
                                                </span>
                                                <span className="text-[11px] font-mono text-stone-400">/ selamanya</span>
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
                                            href={`/checkout/${tier.id}`}
                                            className={`w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl font-bold text-xs sm:text-sm transition-colors min-h-[44px] ${
                                                isBundle
                                                    ? 'bg-[#111113] text-white hover:bg-stone-800'
                                                    : 'border border-[#E5E0D5] bg-[#FAF8F5] text-stone-800 hover:bg-stone-100'
                                            }`}
                                        >
                                            <ShieldCheck className="w-4 h-4" />
                                            <span>Beli Akses Playbook</span>
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
                            PERTANYAAN SERING DIAJUKAN
                        </span>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-[#111113]">
                            Informasi Produk & Format Playbook
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
