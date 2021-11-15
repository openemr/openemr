import { RenderingContext2D } from './types';
import Parser, { IParserOptions } from './Parser';
import Screen, { IScreenOptions, IScreenStartOptions } from './Screen';
import Document, { IDocumentOptions } from './Document';
declare type DOMDocument = typeof window.document;
export interface IOptions extends IParserOptions, IScreenOptions, IScreenStartOptions, IDocumentOptions {
}
/**
 * SVG renderer on canvas.
 */
export default class Canvg {
    /**
     * Create Canvg instance from SVG source string or URL.
     * @param ctx - Rendering context.
     * @param svg - SVG source string or URL.
     * @param options - Rendering options.
     * @returns Canvg instance.
     */
    static from(ctx: RenderingContext2D, svg: string, options?: IOptions): Promise<Canvg>;
    /**
     * Create Canvg instance from SVG source string.
     * @param ctx - Rendering context.
     * @param svg - SVG source string.
     * @param options - Rendering options.
     * @returns Canvg instance.
     */
    static fromString(ctx: RenderingContext2D, svg: string, options?: IOptions): Canvg;
    /**
     * XML/HTML parser instance.
     */
    readonly parser: Parser;
    /**
     * Screen instance.
     */
    readonly screen: Screen;
    /**
     * Canvg Document.
     */
    readonly document: Document;
    private readonly documentElement;
    private readonly options;
    /**
     * Main constructor.
     * @param ctx - Rendering context.
     * @param svg - SVG Document.
     * @param options - Rendering options.
     */
    constructor(ctx: RenderingContext2D, svg: DOMDocument, options?: IOptions);
    /**
     * Create new Canvg instance with inherited options.
     * @param ctx - Rendering context.
     * @param svg - SVG source string or URL.
     * @param options - Rendering options.
     * @returns Canvg instance.
     */
    fork(ctx: RenderingContext2D, svg: string, options?: IOptions): Promise<Canvg>;
    /**
     * Create new Canvg instance with inherited options.
     * @param ctx - Rendering context.
     * @param svg - SVG source string.
     * @param options - Rendering options.
     * @returns Canvg instance.
     */
    forkString(ctx: RenderingContext2D, svg: string, options?: IOptions): Canvg;
    /**
     * Document is ready promise.
     * @returns Ready promise.
     */
    ready(): Promise<void>;
    /**
     * Document is ready value.
     * @returns Is ready or not.
     */
    isReady(): boolean;
    /**
     * Render only first frame, ignoring animations and mouse.
     * @param options - Rendering options.
     */
    render(options?: IScreenStartOptions): Promise<void>;
    /**
     * Start rendering.
     * @param options - Render options.
     */
    start(options?: IScreenStartOptions): void;
    /**
     * Stop rendering.
     */
    stop(): void;
    /**
     * Resize SVG to fit in given size.
     * @param width
     * @param height
     * @param preserveAspectRatio
     */
    resize(width: number, height?: number, preserveAspectRatio?: boolean | string): void;
}
export {};
//# sourceMappingURL=Canvg.d.ts.map