import React, { useEffect, useRef, useState } from 'react';
import { Search, X } from 'lucide-react';
import { ContentRegister, LocaleCode } from '@/types/remotion';

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
    const [query, setQuery] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (isOpen) {
            setTimeout(() => inputRef.current?.focus(), 50);
        } else {
            setQuery('');
        }
    }, [isOpen]);

    // Close on Escape
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && isOpen) {
                onClose();
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4">
            <div
                className="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
                onClick={onClose}
                aria-hidden="true"
            />

            <div
                role="dialog"
                aria-modal="true"
                aria-label="SOS Topik: Cari Skrip & Kerangka Kerja"
                className="relative w-full max-w-xl rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-2xl overflow-hidden z-10 animate-in fade-in zoom-in-95 duration-100"
            >
                <div className="p-3 border-b border-[#E5E0D5] flex items-center gap-2">
                    <Search className="w-4 h-4 text-stone-400 shrink-0 ml-1" />
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Ketik topik darurat (misal: 'staf defensif', 'rumus 3k', 'tolak lembur')..."
                        className="w-full text-sm outline-none bg-transparent placeholder:text-stone-400 text-[#111113]"
                    />
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Tutup pencarian"
                        className="p-1 rounded-md hover:bg-stone-100 text-stone-400 hover:text-stone-600"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                <div className="p-6 text-center text-xs text-stone-500">
                    {query.trim().length < 2 ? (
                        <p>Ketik minimal 2 karakter untuk mencari skrip dialog dan langkah taktis di seluruh 45 bab.</p>
                    ) : (
                        <p>Pencarian Hybrid PostgreSQL 18 aktif untuk kata kunci "{query}".</p>
                    )}
                </div>
            </div>
        </div>
    );
};
