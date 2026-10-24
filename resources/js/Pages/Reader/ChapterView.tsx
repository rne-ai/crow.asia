import React, { useEffect, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import FullWidthWorkspaceLayout, { PaperTheme } from '@/Layouts/FullWidthWorkspaceLayout';
import { TopNavbarFullWidth } from '@/Components/navigation/TopNavbarFullWidth';
import { ReaderMode, ReaderModeSwitch } from '@/Components/reader/ReaderModeSwitch';
import { PreCompiledArticle } from '@/Components/reader/PreCompiledArticle';
import { TacticalRightDock } from '@/Components/reader/TacticalRightDock';
import { PaywallModal } from '@/Components/reader/PaywallModal';
import { SosSearchModal } from '@/Components/reader/SosSearchModal';
import { LandscapeAnimationStage } from '@/Components/remotion/LandscapeAnimationStage';
import { ReaderPageInertiaProps } from '@/types/crow';
import { ContentRegister, LocaleCode } from '@/types/remotion';
import { ArrowLeft, ArrowRight, CheckCircle2, Lock } from 'lucide-react';

export default function ChapterView({
    book,
    locale,
    activeRegister,
    supportedLocales,
    currentChapter,
    allChapters,
}: ReaderPageInertiaProps) {
    const [mode, setMode] = useState<ReaderMode>('read');
    const [paperTheme, setPaperTheme] = useState<PaperTheme>('ivory');
    const [fontSize, setFontSize] = useState<'sm' | 'base' | 'lg'>('base');
    const [teammateName, setTeammateName] = useState<string>('');
    const [checklists, setChecklists] = useState(currentChapter.checklists);
    const [isSearchOpen, setIsSearchOpen] = useState(false);

    // Sync checklists when chapter changes
    useEffect(() => {
        setChecklists(currentChapter.checklists);
    }, [currentChapter.slug, currentChapter.checklists]);

    // Load teammate name preference from localStorage
    useEffect(() => {
        try {
            const savedName = localStorage.getItem('crow:teammate-name');
            if (savedName) setTeammateName(savedName);
        } catch {
            // Ignore storage issues
        }
    }, []);

    const handleTeammateNameChange = (name: string) => {
        setTeammateName(name);
        try {
            localStorage.setItem('crow:teammate-name', name);
        } catch {
            // Ignore
        }
    };

    // Optimistic checklist toggle
    const handleToggleChecklist = (itemIndex: number, currentCompleted: boolean) => {
        const nextCompleted = !currentCompleted;

        // 1. Optimistic local state update
        setChecklists((prev) =>
            prev.map((c) => (c.itemIndex === itemIndex ? { ...c, isCompleted: nextCompleted } : c))
        );

        // 2. Background server sync
        fetch('/api/checklists/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                book_id: book.id,
                chapter_slug: currentChapter.slug,
                item_index: itemIndex,
                is_completed: nextCompleted,
            }),
        }).catch(() => {
            // Revert on error
            setChecklists((prev) =>
                prev.map((c) => (c.itemIndex === itemIndex ? { ...c, isCompleted: currentCompleted } : c))
            );
        });
    };

    const handleRegisterChange = (newRegister: ContentRegister) => {
        const url = new URL(window.location.href);
        url.searchParams.set('reg', newRegister);
        router.visit(url.pathname + url.search, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const handleLocaleChange = (newLocale: LocaleCode) => {
        router.visit(`/read/${book.id}/${newLocale}/${currentChapter.slug}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const prevChapter = allChapters.find((c) => c.index === currentChapter.index - 1);
    const nextChapter = allChapters.find((c) => c.index === currentChapter.index + 1);

    return (
        <FullWidthWorkspaceLayout
            paperTheme={paperTheme}
            onOpenSearch={() => setIsSearchOpen(true)}
            topNavbar={
                <TopNavbarFullWidth
                    currentLocale={locale}
                    supportedLocales={supportedLocales}
                    onLocaleChange={handleLocaleChange}
                    currentBookId={book.id}
                    paperTheme={paperTheme}
                    onTogglePaperTheme={setPaperTheme}
                    onOpenSearch={() => setIsSearchOpen(true)}
                />
            }
            leftSidebar={
                <nav className="space-y-1 font-sans text-xs" aria-label="Daftar Bab">
                    <div className="pb-3 mb-2 border-b border-[#E5E0D5]">
                        <span className="font-mono text-[11px] font-bold uppercase tracking-wider text-stone-500 block">
                            {book.volumeCode}
                        </span>
                        <h2 className="font-bold text-sm text-[#111113] truncate">{book.title}</h2>
                    </div>

                    <div className="space-y-1">
                        {allChapters.map((ch) => {
                            const isCurrent = ch.slug === currentChapter.slug;
                            return (
                                <a
                                    key={ch.slug}
                                    href={`/read/${book.id}/${locale}/${ch.slug}${
                                        locale === 'id' ? `?reg=${activeRegister}` : ''
                                    }`}
                                    className={`flex items-start gap-2.5 p-2 rounded-lg transition-colors ${
                                        isCurrent
                                            ? 'bg-[#111113] text-[#FAF8F5] font-bold shadow-2xs'
                                            : 'hover:bg-black/5 text-stone-700'
                                    }`}
                                >
                                    <span className="mt-0.5 shrink-0">
                                        {ch.isFreeSample ? (
                                            <CheckCircle2
                                                className={`w-3.5 h-3.5 ${
                                                    isCurrent ? 'text-[#FFE66D]' : 'text-emerald-600'
                                                }`}
                                            />
                                        ) : !ch.isUnlocked ? (
                                            <Lock
                                                className={`w-3.5 h-3.5 ${
                                                    isCurrent ? 'text-amber-300' : 'text-stone-400'
                                                }`}
                                            />
                                        ) : (
                                            <span
                                                className={`w-2 h-2 rounded-full inline-block mt-1 ${
                                                    isCurrent ? 'bg-[#FFE66D]' : 'bg-stone-300'
                                                }`}
                                            />
                                        )}
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate leading-tight">{ch.title}</p>
                                        <span
                                            className={`text-[10px] font-mono ${
                                                isCurrent ? 'text-stone-300' : 'text-stone-400'
                                            }`}
                                        >
                                            {ch.isFreeSample ? 'Sampel Gratis' : `Bab ${ch.index}`}
                                        </span>
                                    </div>
                                </a>
                            );
                        })}
                    </div>
                </nav>
            }
            rightDock={
                <TacticalRightDock
                    locale={locale}
                    register={activeRegister}
                    onRegisterChange={handleRegisterChange}
                    teammateName={teammateName}
                    onTeammateNameChange={handleTeammateNameChange}
                    paperTheme={paperTheme}
                    onPaperThemeChange={setPaperTheme}
                    fontSize={fontSize}
                    onFontSizeChange={setFontSize}
                    checklists={checklists}
                    onToggleChecklist={handleToggleChecklist}
                />
            }
        >
            <Head title={`${currentChapter.title} — ${book.volumeCode}`} />

            <div className="w-full max-w-4xl mx-auto space-y-6 pb-20">
                {/* Chapter Header & Mode Switcher */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E5E0D5]">
                    <div>
                        <span className="text-xs font-mono uppercase tracking-wider text-stone-500 font-semibold">
                            {book.volumeCode} • Bab {currentChapter.index} dari 14
                        </span>
                        <h1 className="text-2xl sm:text-3xl font-extrabold text-[#111113] tracking-tight mt-1">
                            {currentChapter.title}
                        </h1>
                    </div>

                    <ReaderModeSwitch mode={mode} onModeChange={setMode} className="shrink-0 self-start sm:self-auto" />
                </div>

                {/* Main Content Area */}
                {mode === 'read' ? (
                    <div className="space-y-6">
                        <PreCompiledArticle
                            html={currentChapter.displayHtml}
                            teammateName={teammateName}
                            fontSize={fontSize}
                        />

                        {/* Paywall Gate for locked chapters */}
                        {!currentChapter.hasAccess && (
                            <PaywallModal
                                volumeCode={book.volumeCode}
                                bookTitle={book.title}
                                bookId={book.id}
                            />
                        )}

                        {/* Chapter Navigation Footer (Prev / Next) */}
                        <div className="pt-8 border-t border-[#E5E0D5] flex items-center justify-between gap-4">
                            {prevChapter ? (
                                <a
                                    href={`/read/${book.id}/${locale}/${prevChapter.slug}${
                                        locale === 'id' ? `?reg=${activeRegister}` : ''
                                    }`}
                                    className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-[#E5E0D5] bg-white text-xs sm:text-sm font-semibold text-stone-700 hover:text-[#111113] hover:bg-stone-50 transition-colors min-h-[40px]"
                                >
                                    <ArrowLeft className="w-4 h-4" />
                                    <span>Bab Sebelumnya</span>
                                </a>
                            ) : (
                                <div />
                            )}

                            {nextChapter && (
                                <a
                                    href={`/read/${book.id}/${locale}/${nextChapter.slug}${
                                        locale === 'id' ? `?reg=${activeRegister}` : ''
                                    }`}
                                    className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#111113] text-[#FAF8F5] text-xs sm:text-sm font-semibold hover:bg-stone-800 transition-colors min-h-[40px]"
                                >
                                    <span>Lanjut: Bab {nextChapter.index}</span>
                                    <ArrowRight className="w-4 h-4" />
                                </a>
                            )}
                        </div>
                    </div>
                ) : (
                    /* Mode 2: Landscape 16:9 Animation Stage */
                    <div className="space-y-6">
                        <LandscapeAnimationStage
                            storyProps={{
                                ...currentChapter.remotionConfig.sceneProps,
                                teammateName: teammateName.trim() || currentChapter.remotionConfig.sceneProps.teammateName,
                            }}
                            durationInFrames={currentChapter.remotionConfig.durationInFrames}
                            optionalYoutube={currentChapter.optionalYoutube}
                        />

                        {/* Paywall Gate for locked chapters in Animation mode */}
                        {!currentChapter.hasAccess && (
                            <PaywallModal
                                volumeCode={book.volumeCode}
                                bookTitle={book.title}
                                bookId={book.id}
                            />
                        )}
                    </div>
                )}
            </div>

            {/* SOS Search Modal */}
            <SosSearchModal
                isOpen={isSearchOpen}
                onClose={() => setIsSearchOpen(false)}
                locale={locale}
                register={activeRegister}
            />
        </FullWidthWorkspaceLayout>
    );
}
