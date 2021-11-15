import { RenderingContext2D } from '../types';
import RenderedElement from './RenderedElement';
export default class SVGElement extends RenderedElement {
    type: string;
    root: boolean;
    setContext(ctx: RenderingContext2D): void;
    clearContext(ctx: RenderingContext2D): void;
    /**
     * Resize SVG to fit in given size.
     * @param width
     * @param height
     * @param preserveAspectRatio
     */
    resize(width: number, height?: number, preserveAspectRatio?: boolean | string): void;
}
//# sourceMappingURL=SVGElement.d.ts.map