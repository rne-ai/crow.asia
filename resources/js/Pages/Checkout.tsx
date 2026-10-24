import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import MarketingFullWidthLayout from '@/Layouts/MarketingFullWidthLayout';
import { ArrowLeft, Check, CheckCircle2, Lock, ShieldCheck, Zap } from 'lucide-react';

interface CheckoutProps {
    tier: {
        id: string;
        name: string;
        description: string;
        priceIdr: number;
        strikePriceIdr: number | null;
        priceUsd: number;
        features: string[];
        isBestValue: boolean;
    };
    locale: string;
    user: { name: string; email: string } | null;
}

export default function Checkout({ tier, locale, user }: CheckoutProps) {
    const [email, setEmail] = useState(user?.email || '');
    const [name, setName] = useState(user?.name || '');
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!email.trim() || !email.includes('@')) {
            setError('Silakan masukkan alamat email yang valid.');
            return;
        }

        setError(null);
        setProcessing(true);

        router.post(
            '/checkout/process',
            {
                pricing_tier_id: tier.id,
                customer_email: email.trim(),
                customer_name: name.trim() || null,
            },
            {
                onError: (errs) => {
                    setError(Object.values(errs)[0] as string);
                    setProcessing(false);
                },
            }
        );
    };

    return (
        <MarketingFullWidthLayout currentLocale="id">
            <Head title={`Checkout: ${tier.name} — crow.asia`} />

            <div className="w-full max-w-4xl mx-auto py-12 px-4 sm:px-6">
                <a
                    href="/#harga"
                    className="inline-flex items-center gap-1.5 text-xs font-mono font-semibold text-stone-500 hover:text-[#111113] mb-6"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Kembali ke Pilihan Paket</span>
                </a>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    {/* Left: Plan Summary Card */}
                    <div className="p-6 sm:p-8 rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-sm space-y-6">
                        <div className="space-y-2">
                            {tier.isBestValue && (
                                <span className="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-extrabold uppercase bg-[#FFE66D] text-[#111113] border border-[#111113]">
                                    ★ PAKET TRILOGI LENGKAP
                                </span>
                            )}
                            <h1 className="text-2xl font-black text-[#111113]">{tier.name}</h1>
                            <p className="text-xs sm:text-sm text-stone-600">{tier.description}</p>
                        </div>

                        <div className="py-3 border-y border-[#E5E0D5]">
                            {tier.strikePriceIdr && (
                                <span className="text-xs line-through text-stone-400 block font-mono">
                                    Rp{tier.strikePriceIdr.toLocaleString('id-ID')}
                                </span>
                            )}
                            <div className="flex items-baseline gap-1">
                                <span className="text-3xl font-black text-[#111113]">
                                    Rp{tier.priceIdr.toLocaleString('id-ID')}
                                </span>
                                <span className="text-xs font-mono text-stone-500">/ sekali bayar</span>
                            </div>
                        </div>

                        <div className="space-y-2.5">
                            <span className="text-xs font-mono font-bold uppercase text-stone-500 block">
                                Fasilitas yang Didapatkan:
                            </span>
                            <ul className="space-y-2 text-xs text-stone-700">
                                {tier.features.map((f, idx) => (
                                    <li key={idx} className="flex items-start gap-2">
                                        <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                        <span>{f}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="p-3.5 rounded-xl bg-[#FAF8F5] border border-[#E5E0D5] flex items-center gap-2.5 text-xs text-stone-600">
                            <ShieldCheck className="w-5 h-5 text-emerald-600 shrink-0" />
                            <span>Akses seumur hidup & pembaruan naskah di masa mendatang tanpa biaya langganan.</span>
                        </div>
                    </div>

                    {/* Right: Checkout Form */}
                    <div className="p-6 sm:p-8 rounded-2xl border border-[#E5E0D5] bg-[#FFFFFF] shadow-sm space-y-6">
                        <div className="space-y-1">
                            <h2 className="text-lg font-bold text-[#111113]">Data Pengiriman Akses</h2>
                            <p className="text-xs text-stone-500">
                                Tautan aktivasi dan hak akses playbook akan dikaitkan ke alamat email ini.
                            </p>
                        </div>

                        {error && (
                            <div className="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 font-medium">
                                {error}
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-1.5">
                                <label htmlFor="customer-email" className="block text-xs font-mono font-bold uppercase text-stone-700">
                                    Alamat Email Aktif <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="customer-email"
                                    type="email"
                                    required
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    placeholder="nama@perusahaan.com"
                                    className="w-full px-3.5 py-2.5 text-sm rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8]"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <label htmlFor="customer-name" className="block text-xs font-mono font-bold uppercase text-stone-700">
                                    Nama Lengkap (Opsional)
                                </label>
                                <input
                                    id="customer-name"
                                    type="text"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    placeholder="Nama Anda"
                                    className="w-full px-3.5 py-2.5 text-sm rounded-lg border border-[#E5E0D5] bg-[#FAF8F5] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8]"
                                />
                            </div>

                            <div className="pt-2">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-[#111113] text-white font-bold text-sm hover:bg-stone-800 disabled:opacity-50 transition-colors min-h-[46px]"
                                >
                                    <Lock className="w-4 h-4" />
                                    <span>{processing ? 'Menyiapkan Pembayaran...' : `Lanjut Pembayaran (Rp${tier.priceIdr.toLocaleString('id-ID')})`}</span>
                                </button>
                            </div>
                        </form>

                        <div className="pt-4 border-t border-[#E5E0D5] text-[11px] font-mono text-stone-500 space-y-1">
                            <p className="flex items-center gap-1.5">
                                <Lock className="w-3 h-3 text-emerald-600" />
                                <span>Pembayaran aman diproses via Mayar.id</span>
                            </p>
                            <p>QRIS • BCA • Mandiri • BRI • BNI • Kartu Kredit</p>
                        </div>
                    </div>
                </div>
            </div>
        </MarketingFullWidthLayout>
    );
}
