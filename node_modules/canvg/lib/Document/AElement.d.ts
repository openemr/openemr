import { RenderingContext2D } from '../types';
import Document from './Document';
import TextElement from './TextElement';
export default class AElement extends TextElement {
    type: string;
    protected readonly hasText: boolean;
    protected readonly text: string;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    getText(): string;
    renderChildren(ctx: RenderingContext2D): void;
    onClick(): void;
    onMouseMove(): void;
}
//# sourceMappingURL=AElement.d.ts.map