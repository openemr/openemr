import { RenderingContext2D } from '../types';
import Transform from '../Transform';
import RenderedElement from './RenderedElement';
import PathElement from './PathElement';
export default class UseElement extends RenderedElement {
    type: string;
    private cachedElement;
    setContext(ctx: RenderingContext2D): void;
    path(ctx: RenderingContext2D): void;
    renderChildren(ctx: RenderingContext2D): void;
    getBoundingBox(ctx: RenderingContext2D): import("..").BoundingBox;
    elementTransform(): Transform;
    protected get element(): PathElement;
}
//# sourceMappingURL=UseElement.d.ts.map