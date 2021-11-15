import { RenderingContext2D } from '../types';
import Document from '../Document';
import Point from '../Point';
import Property from '../Property';
export default class Scale {
    type: string;
    private readonly scale;
    private readonly originX;
    private readonly originY;
    constructor(_: Document, scale: string, transformOrigin: [Property<string>, Property<string>]);
    apply(ctx: RenderingContext2D): void;
    unapply(ctx: RenderingContext2D): void;
    applyToPoint(point: Point): void;
}
//# sourceMappingURL=Scale.d.ts.map