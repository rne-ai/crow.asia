import { ChapterLandscapeStoryProps, ContentRegister, LocaleCode } from './remotion';

export interface ReaderPageInertiaProps {
  book: {
    id: 'book-1' | 'book-2' | 'book-3';
    volumeCode: string;
    accentHex: string;
    title: string;
    subtitle: string;
    priceSingleIdr?: number;
    priceSingleUsd?: number;
  };
  locale: LocaleCode;
  activeRegister: ContentRegister;
  showRegisterSwitcher: boolean;
  supportedLocales: {
    code: LocaleCode;
    name: string;
    nativeName: string;
    flagEmoji: string;
    isLlmTranslated: boolean;
  }[];
  currentChapter: {
    index: number;
    slug: string;
    title: string;
    isFreeSample: boolean;
    hasAccess: boolean;
    hasScripts: boolean;
    illustrationPath: string | null;
    illustrationCaption: string | null;
    displayHtml: string;
    checklists: { itemIndex: number; labelHtml: string; isCompleted: boolean }[];
    remotionConfig: {
      compositionId: string;
      width: 1280;
      height: 720;
      fps: 30;
      durationInFrames: number;
      sceneProps: ChapterLandscapeStoryProps;
    };
    optionalYoutube: {
      isEnabled: boolean;
      videoId: string | null;
    };
  };
  allChapters: {
    index: number;
    slug: string;
    title: string;
    isFreeSample: boolean;
    isUnlocked: boolean;
  }[];
}
