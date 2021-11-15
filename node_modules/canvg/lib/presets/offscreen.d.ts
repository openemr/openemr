import { DOMParser } from './types';
interface IConfig {
    /**
     * XML/HTML parser from string into DOM Document.
     */
    DOMParser?: DOMParser;
}
/**
 * Options preset for `OffscreenCanvas`.
 * @param config - Preset requirements.
 * @param config.DOMParser - XML/HTML parser from string into DOM Document.
 * @returns Preset object.
 */
export declare function offscreen({ DOMParser: DOMParserFallback }?: IConfig): {
    window: null;
    ignoreAnimation: boolean;
    ignoreMouse: boolean;
    DOMParser: DOMParser;
    createCanvas(width: number, height: number): OffscreenCanvas;
    createImage(url: string): Promise<ImageBitmap>;
};
export {};
//# sourceMappingURL=offscreen.d.ts.map