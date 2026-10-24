import React, { useRef, useState } from 'react';
import { Player, PlayerRef } from '@remotion/player';
import { BookCoverProps, BookCoverRemotionComp } from '@/Components/remotion/compositions/BookCoverRemotionComp';

interface RemotionBookCoverPlayerProps {
    bookProps: BookCoverProps;
    className?: string;
}

export const RemotionBookCoverPlayer: React.FC<RemotionBookCoverPlayerProps> = ({
    bookProps,
    className = '',
}) => {
    const playerRef = useRef<PlayerRef>(null);
    const [isHovered, setIsHovered] = useState(false);

    const handleMouseEnter = () => {
        setIsHovered(true);
        playerRef.current?.play();
    };

    const handleMouseLeave = () => {
        setIsHovered(false);
        playerRef.current?.pause();
    };

    return (
        <div
            className={`relative group cursor-pointer transition-transform duration-300 ${className}`}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
            style={{
                aspectRatio: '2 / 3',
                width: '100%',
                maxHeight: 380,
            }}
        >
            <div className="w-full h-full rounded-2xl overflow-hidden shadow-md group-hover:shadow-2xl transition-all duration-300">
                <Player
                    ref={playerRef}
                    component={BookCoverRemotionComp}
                    inputProps={bookProps}
                    durationInFrames={150}
                    fps={30}
                    compositionWidth={400}
                    compositionHeight={600}
                    style={{
                        width: '100%',
                        height: '100%',
                    }}
                    autoPlay={false}
                    loop
                    controls={false}
                />
            </div>

            {/* Hover indicator hint badge */}
            <div
                className={`absolute bottom-3 right-3 px-2 py-0.5 rounded-full bg-black/60 backdrop-blur-xs text-[10px] font-mono text-white/90 transition-opacity duration-300 pointer-events-none ${
                    isHovered ? 'opacity-100' : 'opacity-0'
                }`}
            >
                ✨ Cinematic Motion
            </div>
        </div>
    );
};
