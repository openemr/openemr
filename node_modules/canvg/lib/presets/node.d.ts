import { DOMParser } from './types';
/**
 * `node-canvas` exports.
 */
interface ICanvas {
    createCanvas(width: number, height: number): any;
    loadImage(src: string): Promise<any>;
}
/**
 * WHATWG-compatible `fetch` function.
 */
declare type Fetch = (input: any, config?: any) => Promise<any>;
interface IConfig {
    /**
     * XML/HTML parser from string into DOM Document.
     */
    DOMParser: DOMParser;
    /**
     * `node-canvas` exports.
     */
    canvas: ICanvas;
    /**
     * WHATWG-compatible `fetch` function.
     */
    fetch: Fetch;
}
/**
 * Options preset for `node-canvas`.
 * @param config - Preset requirements.
 * @param config.DOMParser - XML/HTML parser from string into DOM Document.
 * @param config.canvas - `node-canvas` exports.
 * @param config.fetch - WHATWG-compatible `fetch` function.
 * @returns Preset object.
 */
export declare function node({ DOMParser, canvas, fetch }: IConfig): {
    window: null;
    ignoreAnimation: boolean;
    ignoreMouse: boolean;
    DOMParser: DOMParser;
    fetch: Fetch;
    createCanvas: (width: number, height: number) => any;
    createImage: (src: string) => Promise<any>;
};
export {};
//# sourceMappingURL=node.d.ts.map