import Document from './Document';
import Element from './Element';
import FontFaceElement from './FontFaceElement';
import MissingGlyphElement from './MissingGlyphElement';
import GlyphElement from './GlyphElement';
export default class FontElement extends Element {
    type: string;
    readonly isArabic: boolean;
    readonly missingGlyph: MissingGlyphElement;
    readonly glyphs: Record<string, GlyphElement | Record<string, GlyphElement>>;
    readonly horizAdvX: number;
    readonly isRTL: boolean;
    readonly fontFace: FontFaceElement;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    render(): void;
}
//# sourceMappingURL=FontElement.d.ts.map