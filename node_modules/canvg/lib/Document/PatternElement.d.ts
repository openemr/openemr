import { RenderingContext2D } from '../types';
import Property from '../Property';
import Element from './Element';
export default class PatternElement extends Element {
    type: string;
    createPattern(ctx: RenderingContext2D, _: Element, parentOpacityProp: Property): CanvasPattern;
}
//# sourceMappingURL=PatternElement.d.ts.map