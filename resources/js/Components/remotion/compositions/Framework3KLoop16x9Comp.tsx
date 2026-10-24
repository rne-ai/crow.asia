import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';

interface Framework3KLoopProps {
    accentHex?: string;
    teammateName?: string;
}

export const Framework3KLoop16x9Comp: React.FC<Framework3KLoopProps> = ({
    accentHex = '#1D4ED8',
    teammateName = 'Budi',
}) => {
    const frame = useCurrentFrame();
    const { fps } = useVideoConfig();

    // Loop cycle: 3 phases @ 90 frames each = 270 frames loop
    const cycleFrame = frame % 270;
    const activePillar = Math.floor(cycleFrame / 90); // 0: Keluaran, 1: Kriteria, 2: Kalender
    const phaseFrame = cycleFrame % 90;

    const scaleActive = spring({
        frame: phaseFrame,
        fps,
        config: { damping: 12, stiffness: 100 },
    });

    const pillars = [
        {
            number: '01',
            title: 'Keluaran',
            badge: 'Wujud Fisik',
            desc: `Sepakati dokumen akhir atau hasil nyata yang harus selesai di tangan ${teammateName}.`,
            color: '#1D4ED8',
        },
        {
            number: '02',
            title: 'Kriteria Sukses',
            badge: 'Standar Kualitas',
            desc: 'Kunci metrik akurasi, batas revisi, dan ekspektasi kualitas tanpa asumsi.',
            color: '#EA580C',
        },
        {
            number: '03',
            title: 'Kalender',
            badge: 'Tenggat Waktu',
            desc: 'Tetapkan hari, jam, dan zona waktu penyerahan secara tegas tanpa kata "secepatnya".',
            color: '#0F766E',
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
                padding: '40px 60px',
                boxSizing: 'border-box',
            }}
        >
            <header style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <span
                        style={{
                            backgroundColor: '#111113',
                            color: '#FFE66D',
                            fontFamily: '"JetBrains Mono", monospace',
                            fontSize: '12px',
                            fontWeight: 800,
                            padding: '4px 10px',
                            borderRadius: '6px',
                        }}
                    >
                        RUMUS 3K • SIMULASI LINGKARAN
                    </span>
                    <span style={{ fontSize: '16px', fontWeight: 700 }}>
                        Kesepakatan Target Tanpa Asumsi
                    </span>
                </div>
                <span style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: '12px', color: '#71717A' }}>
                    Fase Aktif: Pillar 0{activePillar + 1} / 03
                </span>
            </header>

            <main style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '24px', alignItems: 'center' }}>
                {pillars.map((p, idx) => {
                    const isSelected = activePillar === idx;
                    const cardScale = isSelected ? 1 + (scaleActive * 0.05) : 0.96;
                    const cardOpacity = isSelected ? 1 : 0.65;

                    return (
                        <div
                            key={p.number}
                            style={{
                                backgroundColor: '#FFFFFF',
                                border: isSelected ? `2px solid ${p.color}` : '1px solid #E5E0D5',
                                borderRadius: '16px',
                                padding: '32px 24px',
                                display: 'flex',
                                flexDirection: 'column',
                                gap: '14px',
                                transform: `scale(${cardScale})`,
                                opacity: cardOpacity,
                                boxShadow: isSelected ? '0 10px 25px rgba(0,0,0,0.06)' : 'none',
                            }}
                        >
                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                <span style={{ fontSize: '32px', fontWeight: 900, color: p.color, fontFamily: '"JetBrains Mono", monospace' }}>
                                    {p.number}
                                </span>
                                <span
                                    style={{
                                        fontSize: '11px',
                                        fontWeight: 700,
                                        fontFamily: '"JetBrains Mono", monospace',
                                        backgroundColor: isSelected ? p.color : '#F4F4F5',
                                        color: isSelected ? '#FFFFFF' : '#71717A',
                                        padding: '3px 8px',
                                        borderRadius: '4px',
                                    }}
                                >
                                    {p.badge}
                                </span>
                            </div>

                            <h3 style={{ fontSize: '22px', fontWeight: 800, margin: 0, color: '#111113' }}>
                                {p.title}
                            </h3>

                            <p style={{ fontSize: '14px', lineHeight: 1.5, color: '#52525B', margin: 0 }}>
                                {p.desc}
                            </p>
                        </div>
                    );
                })}
            </main>

            <footer style={{ borderTop: '1px solid #E5E0D5', paddingTop: '16px', display: 'flex', justifyContent: 'space-between', fontSize: '12px', fontFamily: '"JetBrains Mono", monospace', color: '#71717A' }}>
                <span>PERSONA STAF AKTIF: <strong>{teammateName}</strong></span>
                <span>CROW.ASIA FIELD PLAYBOOK STUDIO</span>
            </footer>
        </div>
    );
};
