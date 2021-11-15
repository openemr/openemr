import { RenderingContext2D } from '../types';
import Document from './Document';
import Element from './Element';
export default class FeColorMatrixElement extends Element {
    type: string;
    protected readonly matrix: number[];
    protected readonly includeOpacity: boolean;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    apply(ctx: RenderingContext2D, _x: number, _y: number, width: number, height: number): void;
}
//# sourceMappingURL=FeColorMatrixElement.d.ts.map