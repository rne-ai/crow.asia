import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { BookId, ContentRegister, LocaleCode } from '@/types/remotion';

interface HeroTrilogyProps {
    activeVolume?: BookId;
    locale?: LocaleCode;
    register?: ContentRegister;
    teammateName?: string;
}

export const HeroTrilogy16x9Comp: React.FC<HeroTrilogyProps> = ({
    activeVolume = 'book-1',
    locale = 'id',
    register = 'casual',
    teammateName = 'Budi',
}) => {
    const frame = useCurrentFrame();
    const { fps } = useVideoConfig();

    const entrance = spring({
        frame,
        fps,
        config: { damping: 15, stiffness: 100 },
    });

    const isVol1 = activeVolume === 'book-1';
    const isVol2 = activeVolume === 'book-2';
    const isVol3 = activeVolume === 'book-3';

    const books = [
        {
            id: 'book-1',
            code: 'VOL. 01',
            title: 'Mahir Memimpin Tim',
            sub: 'Transisi Individual Contributor ke Team Lead',
            accent: '#1D4ED8',
            framework: 'Rumus 3K & Kalibrasi L1-L4',
            isActive: isVol1,
        },
        {
            id: 'book-2',
            code: 'VOL. 02',
            title: 'Napas Panjang Memimpin',
            sub: 'Anti-Burnout & Keberlanjutan Energi Tim',
            accent: '#EA580C',
            framework: 'Beban 30/70 & Boundary Shield',
            isActive: isVol2,
        },
        {
            id: 'book-3',
            code: 'VOL. 03',
            title: 'Memimpin Para Leader',
            sub: 'Orkestrasi Manajer & Penyelarasan Eksekutif',
            accent: '#0F766E',
            framework: 'Delegasi Bertingkat & Multi-Tim',
            isActive: isVol3,
        },
    ];

    return (
        <div
            style={{
                width: 1280,
                height: 720,
                backgroundColor: '#FAF8F5',
                color: '#111113',
                fontFamily: '"Plus Jakarta Sans", system-ui, sans-serif',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                padding: '40px 48px',
                boxSizing: 'border-box',
                transform: `scale(${interpolate(entrance, [0, 1], [0.96, 1])})`,
                opacity: entrance,
            }}
        >
            <header style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: '1px solid #E5E0D5', paddingBottom: '16px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <span style={{ fontSize: '24px' }}>🦅</span>
                    <span style={{ fontSize: '18px', fontWeight: 800 }}>crow.asia Interactive Trilogy Studio</span>
                </div>
                <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: '12px', color: '#71717A' }}>
                    1280x720 • 30 FPS • Dynamic Canvas
                </div>
            </header>

            <main style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '24px', alignItems: 'center', flex: 1, margin: '24px 0' }}>
                {books.map((b) => {
                    const cardScale = b.isActive ? 1.04 : 0.96;
                    const cardOpacity = b.isActive ? 1 : 0.7;

                    return (
                        <div
                            key={b.id}
                            style={{
                                backgroundColor: '#FFFFFF',
                                border: b.isActive ? `2px solid ${b.accent}` : '1px solid #E5E0D5',
                                borderRadius: '16px',
                                padding: '28px',
                                height: '380px',
                                boxSizing: 'border-box',
                                display: 'flex',
                                flexDirection: 'column',
                                justifyContent: 'space-between',
                                transform: `scale(${cardScale})`,
                                opacity: cardOpacity,
                                boxShadow: b.isActive ? '0 12px 30px rgba(0,0,0,0.08)' : '0 1px 3px rgba(0,0,0,0.02)',
                            }}
                        >
                            <div>
                                <span
                                    style={{
                                        backgroundColor: b.accent,
                                        color: '#FFFFFF',
                                        fontSize: '11px',
                                        fontFamily: '"JetBrains Mono", monospace',
                                        fontWeight: 800,
                                        padding: '4px 8px',
                                        borderRadius: '4px',
                                    }}
                                >
                                    {b.code}
                                </span>

                                <h3 style={{ fontSize: '22px', fontWeight: 800, margin: '14px 0 8px', color: '#111113' }}>
                                    {b.title}
                                </h3>

                                <p style={{ fontSize: '13px', color: '#52525B', margin: 0, lineHeight: 1.4 }}>
                                    {b.sub}
                                </p>
                            </div>

                            <div style={{ padding: '12px', backgroundColor: '#FAF8F5', borderRadius: '8px', border: '1px solid #E5E0D5', fontSize: '11px', fontFamily: '"JetBrains Mono", monospace' }}>
                                <span style={{ color: '#71717A', display: 'block' }}>KERANGKA UTAMA:</span>
                                <strong style={{ color: '#111113', display: 'block', marginTop: '2px' }}>{b.framework}</strong>
                            </div>
                        </div>
                    );
                })}
            </main>

            <footer style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid #E5E0D5', paddingTop: '14px', fontSize: '12px', fontFamily: '"JetBrains Mono", monospace', color: '#71717A' }}>
                <span>SIMULASI NAMA: <strong>{teammateName}</strong></span>
                <span>PAN-ASIAN LEADERSHIP FIELD PLAYBOOKS</span>
            </footer>
        </div>
    );
};
