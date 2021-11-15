import { RenderingContext2D } from '../types';
import BoundingBox from '../BoundingBox';
import Document from './Document';
import Element from './Element';
import FontElement from './FontElement';
import GlyphElement from './GlyphElement';
import RenderedElement from './RenderedElement';
export default class TextElement extends RenderedElement {
    type: string;
    protected x: number;
    protected y: number;
    private measureCache;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    setContext(ctx: RenderingContext2D, fromMeasure?: boolean): void;
    protected initializeCoordinates(ctx: RenderingContext2D): void;
    getBoundingBox(ctx: RenderingContext2D): BoundingBox;
    protected getFontSize(): number;
    protected getTElementBoundingBox(ctx: RenderingContext2D): BoundingBox;
    getGlyph(font: FontElement, text: string, i: number): GlyphElement;
    getText(): string;
    protected getTextFromNode(node?: ChildNode): string;
    renderChildren(ctx: RenderingContext2D): void;
    protected renderTElementChildren(ctx: RenderingContext2D): void;
    protected getAnchorDelta(ctx: RenderingContext2D, parent: Element, startI: number): number;
    protected adjustChildCoordinates(ctx: RenderingContext2D, textParent: TextElement, parent: Element, i: number): TextElement;
    protected getChildBoundingBox(ctx: RenderingContext2D, textParent: TextElement, parent: Element, i: number): BoundingBox;
    protected renderChild(ctx: RenderingContext2D, textParent: TextElement, parent: Element, i: number): void;
    protected measureTextRecursive(ctx: RenderingContext2D): number;
    protected measureText(ctx: RenderingContext2D): number;
    protected measureTargetText(ctx: RenderingContext2D, targetText: string): number;
}
//# sourceMappingURL=TextElement.d.ts.map