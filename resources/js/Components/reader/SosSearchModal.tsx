import React, { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { ArrowRight, BookOpen, Lock, Loader2, Search, X } from 'lucide-react';
import { ContentRegister, LocaleCode } from '@/types/remotion';
import { useTranslation } from '@/hooks/useTranslation';

export interface SearchResultItem {
    book_id: string;
    volume_code: string;
    accent_hex: string;
    chapter_index: number;
    chapter_slug: string;
    chapter_title: string;
    snippet: string;
    is_free_sample: boolean;
    is_unlocked: boolean;
    url: string;
}

interface SosSearchModalProps {
    isOpen: boolean;
    onClose: () => void;
    locale: LocaleCode;
    register: ContentRegister;
}

export const SosSearchModal: React.FC<SosSearchModalProps> = ({
    isOpen,
    onClose,
    locale,
    register,
}) => {
    const { t } = useTranslation();
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResultItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const [hasSearched, setHasSearched] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    // Auto focus when modal opens
    useEffect(() => {
        if (isOpen) {
            setTimeout(() => inputRef.current?.focus(), 50);
        } else {
            setQuery('');
            setResults([]);
            setHasSearched(false);
            setSelectedIndex(0);
        }
    }, [isOpen]);

    // Debounced search query
    useEffect(() => {
        if (!isOpen) return;
        const trimmed = query.trim();
        if (trimmed.length < 2) {
            setResults([]);
            setLoading(false);
            setHasSearched(false);
            return;
        }

        setLoading(true);
        const timer = setTimeout(() => {
            fetch(`/api/search?q=${encodeURIComponent(trimmed)}&locale=${locale}&register=${register}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => res.json())
                .then((data: SearchResultItem[]) => {
                    setResults(data);
                    setSelectedIndex(0);
                    setHasSearched(true);
                })
                .catch(() => {
                    setResults([]);
                    setHasSearched(true);
                })
                .finally(() => setLoading(false));
        }, 150);

        return () => clearTimeout(timer);
    }, [query, isOpen, locale, register]);

    // Keyboard navigation (ArrowDown, ArrowUp, Enter, Escape)
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (!isOpen) return;

            if (e.key === 'Escape') {
                e.preventDefault();
                onClose();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                setSelectedIndex((prev) => (results.length > 0 ? (prev + 1) % results.length : 0));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setSelectedIndex((prev) => (results.length > 0 ? (prev - 1 + results.length) % results.length : 0));
            } else if (e.key === 'Enter') {
                if (results[selectedIndex]) {
                    e.preventDefault();
                    handleSelectResult(results[selectedIndex]);
                }
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, results, selectedIndex, onClose]);

    const handleSelectResult = (item: SearchResultItem) => {
        onClose();
        router.visit(item.url, { preserveScroll: false });
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4">
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
                onClick={onClose}
                aria-hidden="true"
            />

            {/* Modal Dialog */}
            <div
                role="dialog"
                aria-modal="true"
                aria-label={t('reader.sos_search_label')}
                className="relative w-full max-w-2xl rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-2xl overflow-hidden z-10 flex flex-col max-h-[80vh] animate-in fade-in zoom-in-95 duration-100"
            >
                {/* Search Input Bar */}
                <div className="p-3.5 border-b border-[#E5E0D5] flex items-center gap-2.5 bg-[#FAF8F5]">
                    <Search className="w-5 h-5 text-stone-400 shrink-0 ml-1" />
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder={t('reader.sos_search_placeholder')}
                        className="w-full text-sm sm:text-base outline-none bg-transparent placeholder:text-stone-400 text-[#111113] font-medium"
                    />
                    {loading && <Loader2 className="w-4 h-4 text-[#1D4ED8] animate-spin shrink-0" />}
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Tutup pencarian"
                        className="p-1.5 rounded-lg hover:bg-stone-200 text-stone-400 hover:text-stone-700 transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Results List */}
                <div className="flex-1 overflow-y-auto p-3 space-y-1.5">
                    {query.trim().length < 2 ? (
                        <div className="p-8 text-center text-xs text-stone-500 space-y-2">
                            <span className="text-2xl block">🔍</span>
                            <p className="font-semibold text-stone-700">Pencarian Instan SOS Topik</p>
                            <p className="max-w-sm mx-auto text-stone-400">
                                Cari skrip dialog, rumus kalibrasi, dan langkah taktis di seluruh 45 bab menggunakan mesin pencarian hybrid PostgreSQL 18.
                            </p>
                        </div>
                    ) : loading && results.length === 0 ? (
                        <div className="p-8 text-center text-xs text-stone-500">
                            <Loader2 className="w-6 h-6 mx-auto animate-spin text-[#1D4ED8] mb-2" />
                            <span>Mencari bab & skrip relevan...</span>
                        </div>
                    ) : hasSearched && results.length === 0 ? (
                        <div className="p-8 text-center text-xs text-stone-500 space-y-1">
                            <p className="font-bold text-stone-700">Tidak ada potongan bab yang cocok</p>
                            <p className="text-stone-400">Coba kata kunci lain seperti "3K", "L1", "koreksi", atau "1-on-1".</p>
                        </div>
                    ) : (
                        results.map((item, idx) => {
                            const isSelected = idx === selectedIndex;
                            return (
                                <div
                                    key={`${item.book_id}-${item.chapter_slug}`}
                                    onClick={() => handleSelectResult(item)}
                                    onMouseEnter={() => setSelectedIndex(idx)}
                                    className={`p-3 rounded-xl border transition-all cursor-pointer flex flex-col gap-1.5 ${
                                        isSelected
                                            ? 'bg-[#FAF8F5] border-[#111113] shadow-xs'
                                            : 'bg-white border-[#E5E0D5] hover:border-stone-400'
                                    }`}
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <div className="flex items-center gap-2">
                                            <span
                                                className="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded text-white"
                                                style={{ backgroundColor: item.accent_hex }}
                                            >
                                                {item.volume_code}
                                            </span>
                                            <span className="font-bold text-xs sm:text-sm text-[#111113] truncate">
                                                {item.chapter_title}
                                            </span>
                                        </div>

                                        <div className="flex items-center gap-1.5 shrink-0 text-xs">
                                            {item.is_unlocked ? (
                                                <span className="text-[11px] font-mono font-semibold text-emerald-700 flex items-center gap-1">
                                                    <BookOpen className="w-3 h-3" />
                                                    <span>Terbuka</span>
                                                </span>
                                            ) : (
                                                <span className="text-[11px] font-mono text-stone-400 flex items-center gap-1">
                                                    <Lock className="w-3 h-3 text-amber-500" />
                                                    <span>Terkunci</span>
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Snippet with highlighted keywords */}
                                    <p
                                        className="text-xs text-stone-600 line-clamp-2 leading-relaxed"
                                        dangerouslySetInnerHTML={{ __html: item.snippet }}
                                    />
                                </div>
                            );
                        })
                    )}
                </div>

                {/* Footer instructions */}
                <div className="px-4 py-2 border-t border-[#E5E0D5] bg-[#FAF8F5] flex items-center justify-between text-[11px] font-mono text-stone-500">
                    <div className="flex items-center gap-3">
                        <span><kbd className="bg-white border border-stone-300 px-1 rounded">↑↓</kbd> navigasi</span>
                        <span><kbd className="bg-white border border-stone-300 px-1 rounded">↵</kbd> buka</span>
                        <span><kbd className="bg-white border border-stone-300 px-1 rounded">esc</kbd> tutup</span>
                    </div>
                    <span>PostgreSQL 18 Hybrid Search</span>
                </div>
            </div>
        </div>
    );
};
