import React from 'react';
import { BookOpen, CheckCircle, Lock, ShieldCheck } from 'lucide-react';

interface PaywallModalProps {
    volumeCode: string;
    bookTitle: string;
    bookId: string;
}

export const PaywallModal: React.FC<PaywallModalProps> = ({
    volumeCode,
    bookTitle,
    bookId,
}) => {
    return (
        <div className="w-full max-w-[72ch] mx-auto mt-8 p-6 sm:p-8 rounded-2xl border-2 border-dashed border-[#E5E0D5] bg-[#FFFFFF] shadow-sm text-center space-y-6">
            <div className="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                <Lock className="w-6 h-6" />
            </div>

            <div className="space-y-2">
                <span className="text-xs font-mono font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                    Akses Khusus Pembaca Resmi
                </span>
                <h3 className="text-xl sm:text-2xl font-extrabold text-[#111113]">
                    Buka Akses Penuh: {volumeCode} — {bookTitle}
                </h3>
                <p className="text-sm text-stone-600 max-w-lg mx-auto leading-relaxed">
                    Bab 0 dan Bab 1 dapat dibaca gratis. Untuk membuka Bab 2 hingga Bab 14 lengkap beserta kanvas interaktif Remotion 16:9, skrip 1-on-1, dan master PDF A5, silakan dapatkan akses playbook ini.
                </p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left max-w-md mx-auto text-xs text-stone-700">
                <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>15 Bab Lengkap</span>
                </div>
                <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Mode Animasi 16:9</span>
                </div>
                <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Master PDF A5</span>
                </div>
            </div>

            <div className="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a
                    href={`/#harga`}
                    className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold rounded-lg bg-[#111113] text-[#FAF8F5] hover:bg-stone-800 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] min-h-[44px]"
                >
                    <ShieldCheck className="w-4 h-4" />
                    <span>Buka Akses Playbook (Rp149.000)</span>
                </a>
                <a
                    href="/login"
                    className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] text-stone-700 hover:text-[#111113] transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] min-h-[44px]"
                >
                    <BookOpen className="w-4 h-4" />
                    <span>Sudah Membeli? Masuk</span>
                </a>
            </div>
        </div>
    );
};
