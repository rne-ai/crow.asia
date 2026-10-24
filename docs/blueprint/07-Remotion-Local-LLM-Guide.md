---
document: 07-Remotion-Local-LLM-Guide
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
target_reader: OpenCode Agent (React 19.3 + TypeScript 6.0 + @remotion/player v4.0)
purpose: Strict Technical Guardrails & Code Blueprints for Building 16:9 Landscape @remotion/player Animations without Hallucinations
---

# Panduan Teknis Implementasi `@remotion/player` v4.0 (React 19.3) di `crow.asia`

> **INSTRUKSI WAJIB UNTUK AGENT DI OPENCODE:**
> Baca dokumen ini sebelum menulis atau memodifikasi file apa pun di dalam `resources/js/Components/remotion/`. Pustaka [Remotion](https://github.com/remotion-dev/remotion) dan [`@remotion/player`](https://www.remotion.dev/docs/player) (`^4.0.535`) bekerja di atas sistem *frame deterministik* yang berbeda dari komponen React biasa. Pelanggaran terhadap aturan di bawah ini akan menyebabkan animasi berkedip (*flickering*), macet, atau gagal di-*build* oleh TypeScript 6.0.

---

## 1. 7 Hukum Besi Remotion di Atas React 19.3 (Anti-Hallucination Guardrails)

Saat menulis komponen komposisi Remotion di `crow.asia`, Agent **WAJIB** mematuhi 7 aturan berikut:

1. **DILARANG KERAS Menggunakan CSS Transitions atau CSS `@keyframes` di Dalam Komposisi Remotion**:
   - **SALAH**: `<div className="transition-all duration-500 animate-bounce">` di dalam komponen yang dirender oleh `<Player>`.
   - **BENAR**: Semua pergerakan, perubahan ukuran (`scale`), posisi (`translateY`), dan opasitas (`opacity`) **WAJIB** dihitung secara matematis dari `const frame = useCurrentFrame()` menggunakan fungsi `spring()` atau `interpolate()` dari `'remotion'`.
2. **DILARANG Menggunakan `setTimeout`, `setInterval`, atau `requestAnimationFrame` di Dalam Komposisi**:
   - Waktu di dalam Remotion 100% dikendalikan oleh nomor `frame` saat ini (`0` s/d `durationInFrames - 1`). Jika `fps = 30`, maka detik ke-1 adalah `frame === 30`, detik ke-3 adalah `frame === 90`.
3. **Selalu Gunakan Rasio Landscape `16:9` Standar (`1280 × 720` pada `30 FPS`)**:
   - Seluruh kanvas Mode Animasi di `crow.asia` wajib menggunakan `compositionWidth={1280}`, `compositionHeight={720}`, dan `fps={30}` agar proporsional di dalam *Desktop Full-Width Layout* maupun saat di-*fullscreen* di perangkat mobile/tablet.
4. **Selalu Clamp Nilai `interpolate()`**:
   - Saat memanggil `interpolate(frame, [start, end], [from, to], { extrapolateLeft: 'clamp', extrapolateRight: 'clamp' })`, **selalu sertakan** `{ extrapolateLeft: 'clamp', extrapolateRight: 'clamp' }` agar nilai tidak bocor menjadi negatif atau melebihi batas sesudah frame berakhir.
5. **Sinkronisasi Reaktif dengan `inputProps` (`teammateName`, `locale`, `register`)**:
   - Komponen Remotion harus menerima `inputProps` yang reaktif. Ketika pembaca mengetik nama staf `"Rina"` atau mengubah toggle `Kasual` $\leftrightarrow$ `Standar` di luar player, `<Player inputProps={{ ... }} />` langsung memperbarui teks di dalam kanvas animasi secara *real-time* tanpa me-mount ulang player.
6. **SSR & Hydration Guard (`mounted` State) + `acknowledgeRemotionLicense`**:
   - Di dalam wrapper `LandscapeAnimationStage.tsx`, `<Player>` dari `@remotion/player` hanya boleh dirender setelah komponen ter-mount di client (`const [mounted, setMounted] = useState(false); useEffect(() => setMounted(true), []);`) untuk mencegah *hydration mismatch* pada SSR Inertia.js v3 dan mendukung deteksi `window.matchMedia('(prefers-reduced-motion: reduce)')`.
   - Sertakan prop `acknowledgeRemotionLicense` pada `<Player>` sesuai spesifikasi `@remotion/player` v4.0+.
7. **Gunakan Inline Styles atau SVG Attributes untuk Nilai Dinamis Per-Frame & Hindari `React.forwardRef`**:
   - Jangan membuat class Tailwind dinamis seperti ``className={`translate-y-[${y}px]`}`` karena Tailwind CSS v4.3 tidak mengkompilasi string interpolasi runtime. Gunakan `style={{ transform: `translateY(${y}px) scale(${s})`, opacity }}`.
   - Sesuai standar **React 19.3**, gunakan prop `ref` secara langsung dan jangan membungkus komponen dengan `React.forwardRef`.

---

## 2. Kontrak Data Tipe TypeScript 6.0 (`resources/js/types/remotion.ts`)

Buat file tipe ini terlebih dahulu agar seluruh komposisi Remotion memiliki tipe yang ketat:

```typescript
export type LocaleCode = 'id' | 'en' | 'ms' | 'ja' | 'ko' | 'zh-CN' | 'tl' | 'vi' | 'th';
export type ContentRegister = 'casual' | 'standard';
export type BookId = 'book-1' | 'book-2' | 'book-3';

export interface RemotionStepMarker {
  stepIndex: number;
  label: string;           // Contoh: "01. Masalah Klasik"
  startFrame: number;      // Contoh: 0, 135, 270, 405
  durationFrames: number;  // Contoh: 135 (4.5 detik @ 30fps)
  headline: string;
  subheadline: string;
  bullets?: string[];
  dialogue?: {
    leaderSpeaker: string;
    leaderText: string;    // Mendukung placeholder "{{teammateName}}"
    staffSpeaker: string;
    staffText: string;
  };
}

export interface ChapterLandscapeStoryProps extends Record<string, unknown> {
  bookId: BookId;
  chapterIndex: number;
  chapterTitle: string;
  motifIcon: string;
  locale: LocaleCode;
  register: ContentRegister;
  teammateName: string;
  accentHex: string;
  steps: RemotionStepMarker[];
}
```

---

## 3. Blueprint Komponen Wrapper `LandscapeAnimationStage.tsx` (React 19.3 + Remotion v4.0)

Gunakan pola berikut untuk membungkus `@remotion/player` lengkap dengan **Interactive Step Markers**, **Play/Pause**, **Speed Toggle**, **Fullscreen Landscape**, dan **Optional YouTube Switcher**:

```tsx
import React, { useEffect, useRef, useState } from 'react';
import { Player, PlayerRef, CallbackListener } from '@remotion/player';
import { Play, Pause, RotateCcw, Maximize2, Sparkles, Video } from 'lucide-react';
import { ChapterLandscapeStoryProps } from '@/types/remotion';
import { ChapterInteractiveStory16x9Comp } from './compositions/ChapterInteractiveStory16x9Comp';
import { OptionalYouTubeShieldPlayer } from './OptionalYouTubeShieldPlayer';

interface LandscapeAnimationStageProps {
  storyProps: ChapterLandscapeStoryProps;
  durationInFrames?: number;
  optionalYoutube?: {
    isEnabled: boolean;
    videoId: string | null;
  };
}

export const LandscapeAnimationStage: React.FC<LandscapeAnimationStageProps> = ({
  storyProps,
  durationInFrames = 540,
  optionalYoutube,
}) => {
  const playerRef = useRef<PlayerRef>(null);
  const containerRef = useRef<HTMLDivElement>(null);
  const [mounted, setMounted] = useState(false);
  const [playing, setPlaying] = useState(true);
  const [currentFrame, setCurrentFrame] = useState(0);
  const [playbackRate, setPlaybackRate] = useState<number>(1);
  const [activeSubMode, setActiveSubMode] = useState<'remotion' | 'youtube'>('remotion');

  const showYoutubeTab = Boolean(
    optionalYoutube?.isEnabled && optionalYoutube?.videoId
  );

  useEffect(() => {
    setMounted(true);
    if (typeof window !== 'undefined' && window.matchMedia) {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        setPlaying(false);
      }
    }
  }, []);

  useEffect(() => {
    if (!mounted || activeSubMode !== 'remotion') return;
    const player = playerRef.current;
    if (!player) return;

    const onPlay: CallbackListener<'play'> = () => setPlaying(true);
    const onPause: CallbackListener<'pause'> = () => setPlaying(false);
    const onFrameUpdate: CallbackListener<'frameupdate'> = (e) => {
      setCurrentFrame(e.detail.frame);
    };

    player.addEventListener('play', onPlay);
    player.addEventListener('pause', onPause);
    player.addEventListener('frameupdate', onFrameUpdate);

    return () => {
      player.removeEventListener('play', onPlay);
      player.removeEventListener('pause', onPause);
      player.removeEventListener('frameupdate', onFrameUpdate);
    };
  }, [mounted, activeSubMode]);

  const handleSeekToStep = (startFrame: number) => {
    const player = playerRef.current;
    if (!player) return;
    player.seekTo(startFrame);
    player.play();
  };

  const handleFullscreen = () => {
    if (!containerRef.current) return;
    if (document.fullscreenElement) {
      document.exitFullscreen().catch(() => {});
    } else {
      containerRef.current.requestFullscreen().catch(() => {});
    }
  };

  const activeStepIndex = storyProps.steps.reduce((acc, step, idx) => {
    if (currentFrame >= step.startFrame) return idx;
    return acc;
  }, 0);

  return (
    <div
      ref={containerRef}
      className="w-full rounded-2xl border border-zinc-200 bg-[#FAF8F5] p-3 sm:p-4 shadow-xs"
    >
      {/* Top Bar: Sub-Mode Switcher (Hanya tampil jika YouTube Opsional diaktifkan Admin) */}
      <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div className="flex items-center gap-2">
          <span className="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 font-mono text-xs font-extrabold text-zinc-900">
            <Sparkles className="h-3.5 w-3.5 text-blue-600" />
            <span>STUDIO VISUAL 16:9 • CROW.ASIA</span>
          </span>
        </div>

        {showYoutubeTab && (
          <div className="inline-flex rounded-xl border border-zinc-200 bg-white p-1">
            <button
              type="button"
              onClick={() => setActiveSubMode('remotion')}
              className={`min-h-9 rounded-lg px-3 py-1 font-mono text-xs font-bold transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 ${
                activeSubMode === 'remotion'
                  ? 'bg-zinc-900 text-white'
                  : 'text-zinc-600 hover:text-zinc-900'
              }`}
            >
              ✨ Animasi Interaktif
            </button>
            <button
              type="button"
              onClick={() => {
                playerRef.current?.pause();
                setActiveSubMode('youtube');
              }}
              className={`inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 py-1 font-mono text-xs font-bold transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 ${
                activeSubMode === 'youtube'
                  ? 'bg-zinc-900 text-white'
                  : 'text-zinc-600 hover:text-zinc-900'
              }`}
            >
              <Video className="h-3.5 w-3.5" />
              <span>Video Penjelasan</span>
            </button>
          </div>
        )}
      </div>

      {/* Main 16:9 Landscape Canvas */}
      {activeSubMode === 'youtube' && showYoutubeTab && optionalYoutube?.videoId ? (
        <OptionalYouTubeShieldPlayer videoId={optionalYoutube.videoId} />
      ) : (
        <>
          <div
            className="relative w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-inner"
            style={{ aspectRatio: '16 / 9' }}
          >
            {!mounted ? (
              <div className="flex h-full w-full items-center justify-center font-mono text-xs font-bold text-zinc-500">
                Menyiapkan Kanvas Animasi 16:9...
              </div>
            ) : (
              <Player
                ref={playerRef}
                component={ChapterInteractiveStory16x9Comp}
                inputProps={storyProps}
                durationInFrames={durationInFrames}
                compositionWidth={1280}
                compositionHeight={720}
                fps={30}
                loop
                autoPlay={playing}
                playbackRate={playbackRate}
                initiallyMuted={true}
                numberOfSharedAudioTags={0}
                acknowledgeRemotionLicense
                controls={false}
                clickToPlay={false}
                style={{ width: '100%', height: '100%' }}
              />
            )}
          </div>

          {/* Interactive Step Markers & Playback Controls */}
          <div className="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="grid flex-1 grid-cols-2 gap-1.5 sm:grid-cols-4">
              {storyProps.steps.map((step, idx) => {
                const isActive = activeStepIndex === idx;
                return (
                  <button
                    key={step.stepIndex}
                    type="button"
                    onClick={() => handleSeekToStep(step.startFrame)}
                    className={`min-h-11 rounded-xl border px-3 py-2 text-left transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 ${
                      isActive
                        ? 'border-zinc-900 bg-[#FFE66D] text-zinc-900 shadow-2xs'
                        : 'border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50'
                    }`}
                  >
                    <div className="font-mono text-[10px] font-extrabold uppercase">
                      STEP 0{idx + 1}
                    </div>
                    <div className="line-clamp-1 text-xs font-bold">
                      {step.label}
                    </div>
                  </button>
                );
              })}
            </div>

            <div className="flex items-center justify-end gap-1.5 shrink-0">
              <button
                type="button"
                onClick={() => {
                  const p = playerRef.current;
                  if (!p) return;
                  p.isPlaying() ? p.pause() : p.play();
                }}
                className="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-blue-600"
                title={playing ? 'Jeda' : 'Putar'}
                aria-label={playing ? 'Jeda Animasi' : 'Putar Animasi'}
              >
                {playing ? <Pause className="h-4 w-4" /> : <Play className="h-4 w-4" />}
              </button>
              <button
                type="button"
                onClick={() => handleSeekToStep(0)}
                className="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-blue-600"
                title="Ulangi dari Awal"
                aria-label="Ulangi Animasi dari Awal"
              >
                <RotateCcw className="h-4 w-4" />
              </button>
              <button
                type="button"
                onClick={() =>
                  setPlaybackRate((prev) => (prev === 1 ? 1.25 : prev === 1.25 ? 0.75 : 1))
                }
                className="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white px-2.5 font-mono text-xs font-bold text-zinc-900 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-blue-600"
                aria-label="Ubah Kecepatan Putar"
              >
                {playbackRate}x
              </button>
              <button
                type="button"
                onClick={handleFullscreen}
                className="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-blue-600"
                title="Layar Penuh 16:9"
                aria-label="Layar Penuh 16:9"
              >
                <Maximize2 className="h-4 w-4" />
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
};
```

---

## 4. Pola Penulisan Komposisi `ChapterInteractiveStory16x9Comp.tsx`

Saat Agent mengimplementasikan isi visual `ChapterInteractiveStory16x9Comp.tsx`, ikuti rumus pembagian waktu dan animasi berikut:

1. **Hitung Frame Lokal per Step (`localFrame`)**:
   ```typescript
   const frame = useCurrentFrame();
   const { fps } = useVideoConfig();
   const activeStep =
     steps.slice().reverse().find((s) => frame >= s.startFrame) || steps[0];
   const localFrame = Math.max(0, frame - activeStep.startFrame);
   ```
2. **Gunakan `spring()` pada `localFrame` untuk Animasi Masuk Setiap Step**:
   ```typescript
   const cardEntrance = spring({
     frame: localFrame,
     fps,
     config: { damping: 14, stiffness: 120, mass: 0.9 },
   });
   ```
3. **Injeksi Nama Anggota Tim (`teammateName`) Secara Dinamis**:
   ```typescript
   const resolvedLeaderLine = (activeStep.dialogue?.leaderText || '').replace(
     /\{\{teammateName\}\}/g,
     teammateName.trim() || 'Budi'
   );
   ```
4. **Checklist Verifikasi Mandiri sebelum Menyelesaikan Task Remotion**:
   - Jalankan `npx tsc --noEmit` dan pastikan tidak ada error tipe pada `CallbackListener` maupun `inputProps`.
   - Pastikan tidak ada tag `<video>` atau `<audio>` eksternal yang memicu blokir autoplay browser (`numberOfSharedAudioTags={0}`).
