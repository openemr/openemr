import { RenderingContext2D } from '../types';
import Property from '../Property';
import Document from './Document';
import Element from './Element';
import PathElement from './PathElement';
import StopElement from './StopElement';
export default abstract class GradientElement extends Element {
    readonly attributesToInherit: string[];
    protected readonly stops: StopElement[];
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    abstract getGradient(ctx: RenderingContext2D, element: PathElement): CanvasGradient;
    getGradientUnits(): string;
    createGradient(ctx: RenderingContext2D, element: any, parentOpacityProp: Property): string | CanvasGradient | CanvasPattern;
    protected inheritStopContainer(stopsContainer: Element): void;
    protected addParentOpacity(parentOpacityProp: Property, color: string): string;
}
//# sourceMappingURL=GradientElement.d.ts.map