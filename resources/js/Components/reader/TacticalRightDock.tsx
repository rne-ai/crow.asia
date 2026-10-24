import React from 'react';
import { InContentRegisterToggle } from '@/Components/navigation/InContentRegisterToggle';
import { ScriptPersonalizerInput } from './ScriptPersonalizerInput';
import { PaperTheme } from '@/Layouts/FullWidthWorkspaceLayout';
import { ContentRegister, LocaleCode } from '@/types/remotion';
import { CheckSquare, Type } from 'lucide-react';

interface TacticalRightDockProps {
    locale: LocaleCode;
    register: ContentRegister;
    onRegisterChange?: (register: ContentRegister) => void;
    teammateName: string;
    onTeammateNameChange: (name: string) => void;
    paperTheme: PaperTheme;
    onPaperThemeChange: (theme: PaperTheme) => void;
    fontSize: 'sm' | 'base' | 'lg';
    onFontSizeChange: (size: 'sm' | 'base' | 'lg') => void;
    checklists: Array<{ itemIndex: number; labelHtml: string; isCompleted: boolean }>;
    onToggleChecklist?: (itemIndex: number, currentCompleted: boolean) => void;
    className?: string;
}

export const TacticalRightDock: React.FC<TacticalRightDockProps> = ({
    locale,
    register,
    onRegisterChange,
    teammateName,
    onTeammateNameChange,
    paperTheme,
    onPaperThemeChange,
    fontSize,
    onFontSizeChange,
    checklists,
    onToggleChecklist,
    className = '',
}) => {
    const completedCount = checklists.filter((c) => c.isCompleted).length;
    const totalCount = checklists.length;
    const progressPercent = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;

    return (
        <div className={`space-y-6 text-xs text-stone-800 ${className}`}>
            {/* 1. In-Content Register Switcher (Only when locale === 'id') */}
            {locale === 'id' && (
                <div className="space-y-2 pb-5 border-b border-[#E5E0D5]">
                    <span className="font-mono text-[11px] font-bold uppercase tracking-wider text-stone-500 block">
                        Gaya Bahasa Naskah (ID)
                    </span>
                    <InContentRegisterToggle
                        locale={locale}
                        register={register}
                        onRegisterChange={onRegisterChange}
                        className="w-full justify-center"
                    />
                </div>
            )}

            {/* 2. Script Personalizer Input */}
            <div className="pb-5 border-b border-[#E5E0D5]">
                <ScriptPersonalizerInput
                    teammateName={teammateName}
                    onChange={onTeammateNameChange}
                    defaultName={locale === 'en' ? 'Alex' : 'Budi'}
                />
            </div>

            {/* 3. Paper Theme & Font Size Controls */}
            <div className="space-y-4 pb-5 border-b border-[#E5E0D5]">
                <div className="space-y-1.5">
                    <span className="font-mono text-[11px] font-bold uppercase tracking-wider text-stone-500 block">
                        Tema Kertas Baca
                    </span>
                    <div className="grid grid-cols-3 gap-1.5">
                        <button
                            type="button"
                            onClick={() => onPaperThemeChange('ivory')}
                            className={`px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-colors min-h-[38px] ${
                                paperTheme === 'ivory'
                                    ? 'bg-[#FAF8F5] text-[#111113] border-[#111113] font-bold shadow-2xs'
                                    : 'bg-[#FAF8F5] text-stone-600 border-[#E5E0D5] hover:border-stone-400'
                            }`}
                        >
                            Ivory
                        </button>
                        <button
                            type="button"
                            onClick={() => onPaperThemeChange('sepia')}
                            className={`px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-colors min-h-[38px] ${
                                paperTheme === 'sepia'
                                    ? 'bg-[#F4ECD8] text-[#1C1917] border-[#1C1917] font-bold shadow-2xs'
                                    : 'bg-[#F4ECD8] text-stone-700 border-[#E5E0D5] hover:border-stone-400'
                            }`}
                        >
                            Sepia
                        </button>
                        <button
                            type="button"
                            onClick={() => onPaperThemeChange('obsidian')}
                            className={`px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-colors min-h-[38px] ${
                                paperTheme === 'obsidian'
                                    ? 'bg-[#121316] text-[#F4F4F5] border-white font-bold shadow-2xs'
                                    : 'bg-[#121316] text-stone-300 border-stone-800 hover:border-stone-600'
                            }`}
                        >
                            Obsidian
                        </button>
                    </div>
                </div>

                <div className="space-y-1.5">
                    <span className="font-mono text-[11px] font-bold uppercase tracking-wider text-stone-500 flex items-center gap-1">
                        <Type className="w-3.5 h-3.5" />
                        <span>Ukuran Teks Baca</span>
                    </span>
                    <div className="grid grid-cols-3 gap-1.5">
                        <button
                            type="button"
                            onClick={() => onFontSizeChange('sm')}
                            className={`px-2 py-1 rounded-lg border text-xs transition-colors min-h-[36px] ${
                                fontSize === 'sm'
                                    ? 'bg-[#111113] text-[#FAF8F5] font-bold'
                                    : 'bg-white text-stone-600 border-[#E5E0D5] hover:border-stone-400'
                            }`}
                        >
                            Kecil
                        </button>
                        <button
                            type="button"
                            onClick={() => onFontSizeChange('base')}
                            className={`px-2 py-1 rounded-lg border text-xs transition-colors min-h-[36px] ${
                                fontSize === 'base'
                                    ? 'bg-[#111113] text-[#FAF8F5] font-bold'
                                    : 'bg-white text-stone-600 border-[#E5E0D5] hover:border-stone-400'
                            }`}
                        >
                            Standar
                        </button>
                        <button
                            type="button"
                            onClick={() => onFontSizeChange('lg')}
                            className={`px-2 py-1 rounded-lg border text-xs transition-colors min-h-[36px] ${
                                fontSize === 'lg'
                                    ? 'bg-[#111113] text-[#FAF8F5] font-bold'
                                    : 'bg-white text-stone-600 border-[#E5E0D5] hover:border-stone-400'
                            }`}
                        >
                            Besar
                        </button>
                    </div>
                </div>
            </div>

            {/* 4. Chapter Practice Progress & Checklists */}
            <div className="space-y-3">
                <div className="flex items-center justify-between">
                    <span className="font-mono text-[11px] font-bold uppercase tracking-wider text-stone-500 flex items-center gap-1">
                        <CheckSquare className="w-3.5 h-3.5 text-emerald-600" />
                        <span>Latihan Praktik Bab</span>
                    </span>
                    <span className="font-mono text-xs font-bold text-stone-700">
                        {completedCount}/{totalCount}
                    </span>
                </div>

                {/* Progress bar */}
                <div className="w-full bg-[#E5E0D5] h-2 rounded-full overflow-hidden">
                    <div
                        className="bg-emerald-600 h-full transition-all duration-300"
                        style={{ width: `${progressPercent}%` }}
                    />
                </div>

                {/* Checklists items */}
                {totalCount > 0 ? (
                    <div className="space-y-2 pt-1 max-h-60 overflow-y-auto">
                        {checklists.map((c) => (
                            <label
                                key={c.itemIndex}
                                className="flex items-start gap-2 p-2 rounded-lg hover:bg-black/5 cursor-pointer transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    checked={c.isCompleted}
                                    onChange={() => onToggleChecklist && onToggleChecklist(c.itemIndex, c.isCompleted)}
                                    className="mt-0.5 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                <span
                                    className={`text-xs leading-relaxed ${
                                        c.isCompleted ? 'line-through text-stone-400' : 'text-stone-800'
                                    }`}
                                >
                                    {c.labelHtml}
                                </span>
                            </label>
                        ))}
                    </div>
                ) : (
                    <p className="text-[11px] text-stone-400 italic">
                        Tidak ada item checklist pada bab pendahuluan ini.
                    </p>
                )}
            </div>
        </div>
    );
};
