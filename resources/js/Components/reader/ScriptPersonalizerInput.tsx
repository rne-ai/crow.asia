import React from 'react';
import { Sparkles, User } from 'lucide-react';

interface ScriptPersonalizerInputProps {
    teammateName: string;
    onChange: (name: string) => void;
    defaultName?: string;
    className?: string;
}

export const ScriptPersonalizerInput: React.FC<ScriptPersonalizerInputProps> = ({
    teammateName,
    onChange,
    defaultName = 'Budi',
    className = '',
}) => {
    return (
        <div className={`space-y-1.5 ${className}`}>
            <label
                htmlFor="teammate-name-input"
                className="flex items-center gap-1.5 text-xs font-mono font-bold uppercase tracking-wider text-stone-700"
            >
                <User className="w-3.5 h-3.5 text-[#1D4ED8]" />
                <span>Personalisasi Skrip</span>
            </label>

            <div className="relative">
                <input
                    id="teammate-name-input"
                    type="text"
                    value={teammateName}
                    onChange={(e) => onChange(e.target.value)}
                    placeholder={`Contoh: ${defaultName}`}
                    maxLength={30}
                    className="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-[#E5E0D5] bg-[#FFFFFF] text-[#111113] placeholder:text-stone-400 focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8] transition-colors"
                />
                {teammateName && (
                    <button
                        type="button"
                        onClick={() => onChange('')}
                        aria-label="Reset nama"
                        className="absolute right-2 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 text-xs px-1"
                    >
                        ✕
                    </button>
                )}
            </div>

            <p className="text-[11px] text-stone-500 leading-tight flex items-start gap-1">
                <Sparkles className="w-3 h-3 text-amber-500 shrink-0 mt-0.5" />
                <span>Semua skrip 1-on-1 dan dialog langsung tersinkron memakai nama ini.</span>
            </p>
        </div>
    );
};
