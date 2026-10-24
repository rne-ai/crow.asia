import React from 'react';
import { Img, interpolate, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { BookId } from '@/types/remotion';

export interface BookCoverProps {
    bookId: BookId;
    volumeCode: string;
    title: string;
    subtitle: string;
    accentHex: string;
    illustrationPath: string;
    spineLabel?: string;
    chaptersLabel?: string;
    editionLabel?: string;
}

export const BookCoverRemotionComp: React.FC<BookCoverProps> = ({
    bookId,
    volumeCode,
    title,
    subtitle,
    accentHex,
    illustrationPath,
    spineLabel = 'CROW PLAYBOOK',
    chaptersLabel = '15 Bab + PDF',
    editionLabel = 'ID + EN',
}) => {
    const frame = useCurrentFrame();
    const { durationInFrames } = useVideoConfig();

    // 1. Ken Burns slow breathing zoom (smooth loop)
    const scale = interpolate(
        frame,
        [0, durationInFrames / 2, durationInFrames],
        [1.0, 1.04, 1.0],
        { extrapolateLeft: 'clamp', extrapolateRight: 'clamp' }
    );

    const translateY = interpolate(
        frame,
        [0, durationInFrames / 2, durationInFrames],
        [0, -8, 0],
        { extrapolateLeft: 'clamp', extrapolateRight: 'clamp' }
    );

    // 2. Light Sheen Sweep across the book cover
    const sheenProgress = (frame % 90) / 90;
    const sheenX = interpolate(sheenProgress, [0, 0.5, 1], [-150, 150, 250]);
    const sheenOpacity = interpolate(sheenProgress, [0, 0.25, 0.5, 0.75, 1], [0, 0.45, 0.2, 0, 0]);

    return (
        <div
            style={{
                width: 400,
                height: 600,
                position: 'relative',
                overflow: 'hidden',
                borderRadius: 18,
                backgroundColor: '#111113',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                boxShadow: '0 20px 40px -15px rgba(0,0,0,0.3)',
            }}
        >
            {/* Background Layer: Animated Papercraft Portrait */}
            <div
                style={{
                    position: 'absolute',
                    inset: -15,
                    transform: `scale(${scale}) translateY(${translateY}px)`,
                    transformOrigin: 'center center',
                    willChange: 'transform',
                }}
            >
                <Img
                    src={illustrationPath.startsWith('http') ? illustrationPath : staticFile(illustrationPath)}
                    style={{
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover',
                    }}
                />
            </div>

            {/* Subtle Vignette & Contrast Shading */}
            <div
                style={{
                    position: 'absolute',
                    inset: 0,
                    background:
                        'radial-gradient(circle at 50% 30%, rgba(255,255,255,0.08) 0%, rgba(0,0,0,0.35) 75%, rgba(0,0,0,0.65) 100%)',
                    pointerEvents: 'none',
                }}
            />

            {/* Dynamic Light Sheen Glint Sweep */}
            <div
                style={{
                    position: 'absolute',
                    inset: 0,
                    opacity: sheenOpacity,
                    background: `linear-gradient(115deg, transparent 30%, rgba(255, 255, 255, 0.45) 50%, transparent 70%)`,
                    transform: `translateX(${sheenX}%)`,
                    pointerEvents: 'none',
                }}
            />

            {/* Left Spine Fold & 3D Shadow Strip */}
            <div
                style={{
                    position: 'absolute',
                    top: 0,
                    bottom: 0,
                    left: 0,
                    width: 28,
                    backgroundColor: accentHex,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    boxShadow: 'inset -3px 0 6px rgba(0,0,0,0.4), 2px 0 5px rgba(0,0,0,0.2)',
                    zIndex: 10,
                }}
            >
                <span
                    style={{
                        writingMode: 'vertical-rl',
                        transform: 'rotate(180deg)',
                        fontSize: 9,
                        letterSpacing: '0.22em',
                        fontFamily: 'JetBrains Mono, monospace',
                        fontWeight: 900,
                        color: '#FFFFFF',
                        textTransform: 'uppercase',
                        opacity: 0.95,
                    }}
                >
                    {spineLabel}
                </span>
            </div>

            {/* Foreground Paper Placard (White editorial booklet card in center) */}
            <div
                style={{
                    position: 'absolute',
                    top: 24,
                    bottom: 24,
                    left: 44,
                    right: 24,
                    borderRadius: 14,
                    backgroundColor: 'rgba(255, 255, 255, 0.94)',
                    backdropFilter: 'blur(8px)',
                    border: '1px solid rgba(229, 224, 213, 0.9)',
                    boxShadow: '0 8px 24px rgba(0, 0, 0, 0.12)',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'space-between',
                    padding: '20px 18px',
                    zIndex: 5,
                }}
            >
                {/* Placard Top: Volume Pill & Chapters Label */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        borderBottom: '1px solid #EFEAE1',
                        paddingBottom: 12,
                    }}
                >
                    <span
                        style={{
                            padding: '3px 8px',
                            borderRadius: 6,
                            backgroundColor: '#FAF8F5',
                            border: '1px solid #E5E0D5',
                            fontSize: 10,
                            fontFamily: 'JetBrains Mono, monospace',
                            fontWeight: 800,
                            color: '#111113',
                            letterSpacing: '0.04em',
                        }}
                    >
                        {volumeCode}
                    </span>
                    <span
                        style={{
                            fontSize: 10,
                            fontStyle: 'italic',
                            color: '#71717A',
                            fontFamily: 'Newsreader, serif',
                        }}
                    >
                        {chaptersLabel}
                    </span>
                </div>

                {/* Placard Middle: Emblem & Book Title/Subtitle */}
                <div
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        textAlign: 'center',
                        padding: '10px 0',
                    }}
                >
                    <div
                        style={{
                            width: 48,
                            height: 48,
                            borderRadius: 10,
                            backgroundColor: '#FAF8F5',
                            border: '1px solid #E5E0D5',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            marginBottom: 16,
                            boxShadow: '0 2px 6px rgba(0,0,0,0.03)',
                        }}
                    >
                        <span style={{ fontSize: 22 }}>
                            {bookId === 'book-1' ? '🧭' : bookId === 'book-2' ? '🛡️' : '👑'}
                        </span>
                    </div>

                    <h2
                        style={{
                            fontSize: 18,
                            fontWeight: 900,
                            color: '#111113',
                            lineHeight: 1.25,
                            margin: '0 0 8px 0',
                            letterSpacing: '-0.02em',
                        }}
                    >
                        {title}
                    </h2>

                    <p
                        style={{
                            fontSize: 11,
                            color: '#52525B',
                            lineHeight: 1.45,
                            margin: 0,
                            maxWidth: '90%',
                        }}
                    >
                        {subtitle}
                    </p>
                </div>

                {/* Placard Bottom: Brand & Edition */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        borderTop: '1px solid #EFEAE1',
                        paddingTop: 10,
                        fontSize: 9,
                        fontFamily: 'JetBrains Mono, monospace',
                        fontWeight: 700,
                        color: '#71717A',
                    }}
                >
                    <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                        <span style={{ width: 6, height: 6, borderRadius: '50%', backgroundColor: accentHex }} />
                        <span>crow.asia</span>
                    </div>
                    <span>{editionLabel}</span>
                </div>
            </div>
        </div>
    );
};
