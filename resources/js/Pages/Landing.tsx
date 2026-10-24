import React from 'react';
import MarketingFullWidthLayout from '@/Layouts/MarketingFullWidthLayout';
import { BookOpen, Compass, Shield, Zap } from 'lucide-react';

export default function Landing() {
    return (
        <MarketingFullWidthLayout>
            <div className="w-full px-4 sm:px-6 lg:px-12 py-12 md:py-16 max-w-7xl mx-auto">
                <div className="flex flex-col lg:flex-row gap-12 items-center">
                    {/* Left Column: Hero Copy */}
                    <div className="flex-1 space-y-6">
                        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-[#E5E0D5] bg-[#FFFFFF] text-xs font-mono font-medium text-stone-700">
                            <span className="w-2 h-2 rounded-full bg-[#1D4ED8]" />
                            <span>TACTICAL LEADERSHIP FIELD PLAYBOOK</span>
                        </div>

                        <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#111113] leading-[1.15]">
                            Baru Jadi Leader atau Mulai Kewalahan Ngurusin Tim?
                            <span className="block text-[#1D4ED8] mt-2">Ini Contekan Meja Kerja Lo.</span>
                        </h1>

                        <p className="text-base sm:text-lg text-stone-600 max-w-xl leading-relaxed">
                            Bukan buku motivasi teoretis. Ini playbook taktis 1-on-1, rumus kalibrasi tugas, dan skrip siap salin untuk membangun tim mandiri tanpa burnout.
                        </p>

                        <div className="flex flex-wrap gap-4 pt-2">
                            <a
                                href="/read/book-1/id/ch-0"
                                className="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-[#111113] text-[#FAF8F5] font-semibold text-sm hover:bg-stone-800 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] min-h-[44px]"
                            >
                                <BookOpen className="w-4 h-4" />
                                <span>Baca Gratis Bab 0 & 1</span>
                            </a>
                            <a
                                href="#katalog"
                                className="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-[#E5E0D5] bg-[#FFFFFF] text-[#111113] font-semibold text-sm hover:bg-stone-50 transition-colors focus-visible:outline-2 focus-visible:outline-[#1D4ED8] min-h-[44px]"
                            >
                                <span>Lihat 3 Playbook</span>
                            </a>
                        </div>
                    </div>

                    {/* Right Column: Tactical Showcase Cards */}
                    <div className="w-full lg:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div className="p-5 rounded-xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-xs space-y-3">
                            <div className="w-9 h-9 rounded-lg bg-[#1D4ED8]/10 text-[#1D4ED8] flex items-center justify-center font-bold">
                                <Compass className="w-5 h-5" />
                            </div>
                            <h2 className="font-bold text-base text-[#111113]">VOL. 01: Mahir Memimpin</h2>
                            <p className="text-xs text-stone-600 leading-relaxed">
                                Transisi dari kerja sendiri ke mengelola tim. Kuasai Rumus 3K dan Kalibrasi L1–L4.
                            </p>
                        </div>

                        <div className="p-5 rounded-xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-xs space-y-3">
                            <div className="w-9 h-9 rounded-lg bg-[#EA580C]/10 text-[#EA580C] flex items-center justify-center font-bold">
                                <Shield className="w-5 h-5" />
                            </div>
                            <h2 className="font-bold text-base text-[#111113]">VOL. 02: Anti-Burnout</h2>
                            <p className="text-xs text-stone-600 leading-relaxed">
                                Jaga napas panjang kepemimpinan. Terapkan batas beban 30/70 dan audit energi harian.
                            </p>
                        </div>

                        <div className="p-5 rounded-xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-xs space-y-3 sm:col-span-2">
                            <div className="w-9 h-9 rounded-lg bg-[#0F766E]/10 text-[#0F766E] flex items-center justify-center font-bold">
                                <Zap className="w-5 h-5" />
                            </div>
                            <h2 className="font-bold text-base text-[#111113]">VOL. 03: Memimpin Para Leader</h2>
                            <p className="text-xs text-stone-600 leading-relaxed">
                                Dari mengelola teknis ke orkestrasi lintas tim, penyelarasan eksekutif, dan kaderisasi manajer.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </MarketingFullWidthLayout>
    );
}
