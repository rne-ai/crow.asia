import React from 'react';
import { Copy, X } from 'lucide-react';

interface ShareQuoteModalProps {
    isOpen: boolean;
    onClose: () => void;
    quoteText: string;
    chapterTitle: string;
}

export const ShareQuoteModal: React.FC<ShareQuoteModalProps> = ({
    isOpen,
    onClose,
    quoteText,
    chapterTitle,
}) => {
    const [copied, setCopied] = React.useState(false);

    if (!isOpen) return null;

    const handleCopy = () => {
        const fullText = `"${quoteText}"\n\n— Dari ${chapterTitle} (crow.asia)`;
        navigator.clipboard.writeText(fullText).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div className="fixed inset-0 bg-black/40 backdrop-blur-xs" onClick={onClose} aria-hidden="true" />
            <div className="relative w-full max-w-md rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] p-6 shadow-2xl z-10 space-y-4">
                <div className="flex items-center justify-between">
                    <span className="font-mono text-xs font-bold uppercase text-stone-500">Bagikan Kutipan Taktis</span>
                    <button type="button" onClick={onClose} className="p-1 rounded text-stone-400 hover:text-stone-700">
                        <X className="w-4 h-4" />
                    </button>
                </div>
                <blockquote className="p-4 rounded-xl bg-[#FAF8F5] border-l-4 border-[#1D4ED8] text-sm italic text-stone-800">
                    "{quoteText}"
                </blockquote>
                <div className="flex justify-end gap-2">
                    <button
                        type="button"
                        onClick={handleCopy}
                        className="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#111113] text-white text-xs font-semibold hover:bg-stone-800 transition-colors"
                    >
                        <Copy className="w-3.5 h-3.5" />
                        <span>{copied ? 'Tersalin!' : 'Salin Kutipan'}</span>
                    </button>
                </div>
            </div>
        </div>
    );
};
