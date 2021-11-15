import { RenderingContext2D } from '../types';
import Document from '../Document';
import Property from '../Property';
import Point from '../Point';
export default class Rotate {
    type: string;
    private readonly angle;
    private readonly originX;
    private readonly originY;
    private readonly cx;
    private readonly cy;
    constructor(document: Document, rotate: string, transformOrigin: [Property<string>, Property<string>]);
    apply(ctx: RenderingContext2D): void;
    unapply(ctx: RenderingContext2D): void;
    applyToPoint(point: Point): void;
}
//# sourceMappingURL=Rotate.d.ts.map