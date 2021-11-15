import { RenderingContext2D } from '../types';
import Property from '../Property';
import Document from './Document';
export default abstract class Element {
    protected readonly document: Document;
    protected readonly node: HTMLElement;
    protected readonly captureTextNodes: boolean;
    static readonly ignoreChildTypes: string[];
    readonly type: string;
    readonly attributes: Record<string, Property>;
    readonly styles: Record<string, Property>;
    readonly stylesSpecificity: Record<string, string>;
    animationFrozen: boolean;
    animationFrozenValue: string;
    parent: Element;
    children: Element[];
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    getAttribute(name: string, createIfNotExists?: boolean): Property<any>;
    getHrefAttribute(): Property<any>;
    getStyle(name: string, createIfNotExists?: boolean, skipAncestors?: boolean): Property;
    render(ctx: RenderingContext2D): void;
    setContext(_: RenderingContext2D): void;
    protected applyEffects(ctx: RenderingContext2D): void;
    clearContext(_: RenderingContext2D): void;
    renderChildren(ctx: RenderingContext2D): void;
    protected addChild(childNode: Element | HTMLElement): void;
    protected matchesSelector(selector: string): boolean;
    addStylesFromStyleDefinition(): void;
    protected removeStyles(element: Element, ignoreStyles: string[]): [string, string][];
    protected restoreStyles(element: Element, styles: [string, string][]): void;
}
//# sourceMappingURL=Element.d.ts.map