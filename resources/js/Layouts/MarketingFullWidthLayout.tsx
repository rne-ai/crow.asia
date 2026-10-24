import React from 'react';
import { BookOpen, Compass, ShieldCheck } from 'lucide-react';

interface MarketingFullWidthLayoutProps {
    children: React.ReactNode;
    headerRight?: React.ReactNode;
}

export default function MarketingFullWidthLayout({
    children,
    headerRight,
}: MarketingFullWidthLayoutProps) {
    return (
        <div className="min-h-screen w-full flex flex-col bg-[#FAF8F5] text-[#111113] font-sans antialiased selection:bg-[#FFE66D] selection:text-[#111113]">
            {/* Top Navbar Full-Width */}
            <header className="sticky top-0 z-40 w-full border-b border-[#E5E0D5] bg-[#FAF8F5]/95 backdrop-blur-sm">
                <div className="w-full px-4 sm:px-6 lg:px-10 h-16 flex items-center justify-between gap-4">
                    <div className="flex items-center gap-6">
                        <a
                            href="/"
                            className="flex items-center gap-2 font-bold text-lg tracking-tight text-[#111113] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] rounded-md px-1"
                        >
                            <span className="text-xl">🦅</span>
                            <span>crow<span className="text-[#1D4ED8]">.asia</span></span>
                            <span className="hidden sm:inline-block text-[11px] font-mono uppercase tracking-wider bg-[#E5E0D5]/60 text-[#111113] px-2 py-0.5 rounded border border-[#E5E0D5]">
                                Playbook Taktis
                            </span>
                        </a>

                        <nav className="hidden md:flex items-center gap-5 text-sm font-medium text-stone-700">
                            <a
                                href="#katalog"
                                className="hover:text-[#111113] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] rounded px-1 py-0.5 transition-colors"
                            >
                                3 Playbook
                            </a>
                            <a
                                href="#diagnosis"
                                className="hover:text-[#111113] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] rounded px-1 py-0.5 transition-colors"
                            >
                                Diagnosis 45 Detik
                            </a>
                            <a
                                href="#demo-skrip"
                                className="hover:text-[#111113] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] rounded px-1 py-0.5 transition-colors"
                            >
                                Demo Skrip 1-on-1
                            </a>
                            <a
                                href="#harga"
                                className="hover:text-[#111113] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] rounded px-1 py-0.5 transition-colors"
                            >
                                Paket Harga
                            </a>
                        </nav>
                    </div>

                    <div className="flex items-center gap-3">
                        {headerRight ? (
                            headerRight
                        ) : (
                            <a
                                href="/read/book-1/id/ch-0"
                                className="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-[#111113] text-[#FAF8F5] hover:bg-stone-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1D4ED8] transition-colors min-h-[38px]"
                            >
                                <BookOpen className="w-4 h-4" />
                                <span>Baca Bab 0 & 1</span>
                            </a>
                        )}
                    </div>
                </div>
            </header>

            {/* Main Content (Full-Width Canvas) */}
            <main className="w-full flex-1">
                {children}
            </main>

            {/* Tactile Footer Full-Width */}
            <footer className="w-full border-t border-[#E5E0D5] bg-[#FFFFFF] py-12 px-4 sm:px-6 lg:px-10 text-xs sm:text-sm text-stone-600">
                <div className="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div className="space-y-2">
                        <div className="flex items-center gap-2 font-bold text-base text-[#111113]">
                            <span>🦅 crow.asia</span>
                            <span className="font-normal text-xs text-stone-500">— Pan-Asian Interactive Leadership Field Playbook</span>
                        </div>
                        <p className="max-w-xl text-stone-500 text-xs leading-relaxed">
                            Buku pegangan meja kerja taktis untuk para pemimpin tim, manajer rekayasa, dan eksekutif. Ditulis berbasis studi lapangan nyata dan riset perilaku organisasi teruji.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-4 text-xs font-mono text-stone-600">
                        <span className="inline-flex items-center gap-1 bg-[#FAF8F5] px-2.5 py-1 rounded border border-[#E5E0D5]">
                            <Compass className="w-3.5 h-3.5 text-[#1D4ED8]" /> VOL.01 Mahir Memimpin
                        </span>
                        <span className="inline-flex items-center gap-1 bg-[#FAF8F5] px-2.5 py-1 rounded border border-[#E5E0D5]">
                            <ShieldCheck className="w-3.5 h-3.5 text-[#EA580C]" /> VOL.02 Anti-Burnout
                        </span>
                        <span className="inline-flex items-center gap-1 bg-[#FAF8F5] px-2.5 py-1 rounded border border-[#E5E0D5]">
                            <BookOpen className="w-3.5 h-3.5 text-[#0F766E]" /> VOL.03 Orkestrasi
                        </span>
                    </div>
                </div>

                <div className="mt-8 pt-6 border-t border-[#E5E0D5]/60 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-stone-400">
                    <p>© 2026 crow.asia. Seluruh hak cipta dilindungi undang-undang.</p>
                    <p className="font-mono text-[11px]">Desktop 100vw • Dual-Register • Remotion 16:9 • WCAG AA</p>
                </div>
            </footer>
        </div>
    );
}
