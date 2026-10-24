import React, { useEffect, useRef, useState } from 'react';
import { Check, Copy } from 'lucide-react';

interface PreCompiledArticleProps {
    html: string;
    teammateName?: string;
    fontSize?: 'sm' | 'base' | 'lg';
    className?: string;
}

export const PreCompiledArticle: React.FC<PreCompiledArticleProps> = ({
    html,
    teammateName = '',
    fontSize = 'base',
    className = '',
}) => {
    const articleRef = useRef<HTMLDivElement>(null);
    const [copiedIndex, setCopiedIndex] = useState<number | null>(null);

    // Instant Script Personalizer DOM Update (< 2ms)
    useEffect(() => {
        if (!articleRef.current) return;
        const tokens = articleRef.current.querySelectorAll<HTMLElement>('.teammate-token');
        tokens.forEach((el) => {
            const defaultVal = el.dataset.default || 'Budi';
            el.textContent = teammateName.trim() ? teammateName.trim() : defaultVal;
        });
    }, [teammateName, html]);

    // Attach copy button to each script drawer (<details class="crow-script-drawer">)
    useEffect(() => {
        if (!articleRef.current) return;
        const drawers = articleRef.current.querySelectorAll<HTMLDetailsElement>('details.crow-script-drawer');

        drawers.forEach((drawer, idx) => {
            if (drawer.querySelector('.crow-copy-btn')) return;

            const summary = drawer.querySelector('summary');
            if (summary) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'crow-copy-btn inline-flex items-center gap-1 ml-auto text-xs font-mono font-medium px-2 py-0.5 rounded border border-[#E5E0D5] bg-[#FAF8F5] text-stone-700 hover:text-[#111113] hover:bg-white transition-colors cursor-pointer';
                btn.innerHTML = '📋 Salin Skrip';
                btn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    // Clone drawer, remove summary and buttons, get text
                    const clone = drawer.cloneNode(true) as HTMLElement;
                    clone.querySelector('summary')?.remove();
                    clone.querySelectorAll('.crow-copy-btn').forEach((b) => b.remove());
                    const cleanText = clone.innerText.trim();

                    navigator.clipboard.writeText(cleanText).then(() => {
                        setCopiedIndex(idx);
                        btn.innerHTML = '✓ Tersalin!';
                        setTimeout(() => {
                            btn.innerHTML = '📋 Salin Skrip';
                            setCopiedIndex(null);
                        }, 2000);
                    });
                };

                summary.style.display = 'flex';
                summary.style.alignItems = 'center';
                summary.style.justifyContent = 'space-between';
                summary.appendChild(btn);
            }
        });
    }, [html]);

    const fontSizeClass =
        fontSize === 'sm'
            ? 'text-sm leading-relaxed'
            : fontSize === 'lg'
            ? 'text-lg leading-loose'
            : 'text-base leading-relaxed';

    return (
        <div className={`w-full ${className}`}>
            <article
                ref={articleRef}
                className={`max-w-[72ch] mx-auto prose prose-stone font-sans ${fontSizeClass} selection:bg-[#FFE66D] selection:text-[#111113]`}
                dangerouslySetInnerHTML={{ __html: html }}
            />
        </div>
    );
};
