import { RenderingContext2D } from '../types';
import Document from './Document';
import Element from './Element';
export default class FeGaussianBlurElement extends Element {
    type: string;
    readonly extraFilterDistance: number;
    protected readonly blurRadius: number;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    apply(ctx: RenderingContext2D, x: number, y: number, width: number, height: number): void;
}
//# sourceMappingURL=FeGaussianBlurElement.d.ts.map