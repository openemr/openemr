import Canvg from '../Canvg';
import Screen, { IScreenViewBoxConfig } from '../Screen';
import Property from '../Property';
import SVGFontLoader from '../SVGFontLoader';
import Element from './Element';
import TextNode from './TextNode';
import ImageElement from './ImageElement';
import SVGElement from './SVGElement';
/**
 * Function to create new canvas.
 */
export declare type CreateCanvas = (width: number, height: number) => HTMLCanvasElement | OffscreenCanvas;
/**
 * Function to create new image.
 */
export declare type CreateImage = (src: string, anonymousCrossOrigin?: boolean) => Promise<CanvasImageSource>;
export interface IDocumentOptions {
    /**
     * Default `rem` size.
     */
    rootEmSize?: number;
    /**
     * Default `em` size.
     */
    emSize?: number;
    /**
     * Function to create new canvas.
     */
    createCanvas?: CreateCanvas;
    /**
     * Function to create new image.
     */
    createImage?: CreateImage;
    /**
     * Load images anonymously.
     */
    anonymousCrossOrigin?: boolean;
}
export declare type IViewBoxConfig = Omit<IScreenViewBoxConfig, 'document'>;
declare type DOMDocument = typeof window.document;
declare function createCanvas(width: number, height: number): HTMLCanvasElement;
declare function createImage(src: string, anonymousCrossOrigin?: boolean): Promise<HTMLImageElement>;
export default class Document {
    readonly canvg: Canvg;
    static readonly createCanvas: typeof createCanvas;
    static readonly createImage: typeof createImage;
    static readonly elementTypes: {
        svg: typeof SVGElement;
        rect: typeof import("./RectElement").default;
        circle: typeof import("./CircleElement").default;
        ellipse: typeof import("./EllipseElement").default;
        line: typeof import("./LineElement").default;
        polyline: typeof import("./PolylineElement").default;
        polygon: typeof import("./PolygonElement").default;
        path: typeof import("./PathElement").default;
        pattern: typeof import("./PatternElement").default;
        marker: typeof import("./MarkerElement").default;
        defs: typeof import("./DefsElement").default;
        linearGradient: typeof import("./LinearGradientElement").default;
        radialGradient: typeof import("./RadialGradientElement").default;
        stop: typeof import("./StopElement").default;
        animate: typeof import("./AnimateElement").default;
        animateColor: typeof import("./AnimateColorElement").default;
        animateTransform: typeof import("./AnimateTransformElement").default;
        font: typeof import("./FontElement").default;
        'font-face': typeof import("./FontFaceElement").default;
        'missing-glyph': typeof import("./MissingGlyphElement").default;
        glyph: typeof import("./GlyphElement").default;
        text: typeof import("./TextElement").default;
        tspan: typeof import("./TSpanElement").default;
        tref: typeof import("./TRefElement").default;
        a: typeof import("./AElement").default;
        textPath: typeof import("./TextPathElement").default;
        image: typeof ImageElement;
        g: typeof import("./GElement").default;
        symbol: typeof import("./SymbolElement").default;
        style: typeof import("./StyleElement").default;
        use: typeof import("./UseElement").default;
        mask: typeof import("./MaskElement").default;
        clipPath: typeof import("./ClipPathElement").default;
        filter: typeof import("./FilterElement").default;
        feDropShadow: typeof import("./FeDropShadowElement").default;
        feMorphology: typeof import("./FeMorphologyElement").default;
        feComposite: typeof import("./FeCompositeElement").default;
        feColorMatrix: typeof import("./FeColorMatrixElement").default;
        feGaussianBlur: typeof import("./FeGaussianBlurElement").default;
        title: typeof import("./TitleElement").default;
        desc: typeof import("./DescElement").default;
    };
    rootEmSize: number;
    documentElement: SVGElement;
    readonly screen: Screen;
    readonly createCanvas: CreateCanvas;
    readonly createImage: CreateImage;
    readonly definitions: Record<string, Element>;
    readonly styles: Record<string, Record<string, Property>>;
    readonly stylesSpecificity: Record<string, string>;
    readonly images: ImageElement[];
    readonly fonts: SVGFontLoader[];
    private readonly emSizeStack;
    private uniqueId;
    constructor(canvg: Canvg, { rootEmSize, emSize, createCanvas, createImage, anonymousCrossOrigin }?: IDocumentOptions);
    private bindCreateImage;
    get window(): Window;
    get fetch(): typeof fetch;
    get ctx(): import("..").RenderingContext2D;
    get emSize(): number;
    set emSize(value: number);
    popEmSize(): void;
    getUniqueId(): string;
    isImagesLoaded(): boolean;
    isFontsLoaded(): boolean;
    createDocumentElement(document: DOMDocument): SVGElement;
    createElement<T extends Element>(node: HTMLElement): T;
    createTextNode(node: HTMLElement): TextNode;
    setViewBox(config: IViewBoxConfig): void;
}
export {};
//# sourceMappingURL=Document.d.ts.map