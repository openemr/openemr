import { RenderingContext2D } from '../types';
import Document from './Document';
import Element from './Element';
export default class FeDropShadowElement extends Element {
    type: string;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    apply(_: RenderingContext2D, _x: number, _y: number, _width: number, _height: number): void;
}
//# sourceMappingURL=FeDropShadowElement.d.ts.map