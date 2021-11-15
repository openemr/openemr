import { RenderingContext2D } from '../types';
import Document from './Document';
import PathElement from './PathElement';
import GradientElement from './GradientElement';
export default class LinearGradientElement extends GradientElement {
    type: string;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    getGradient(ctx: RenderingContext2D, element: PathElement): CanvasGradient;
}
//# sourceMappingURL=LinearGradientElement.d.ts.map