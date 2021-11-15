import { RenderingContext2D } from '../types';
import Document from '../Document';
import Point from '../Point';
export default class Translate {
    type: string;
    private readonly point;
    constructor(_: Document, point: string);
    apply(ctx: RenderingContext2D): void;
    unapply(ctx: RenderingContext2D): void;
    applyToPoint(point: Point): void;
}
//# sourceMappingURL=Translate.d.ts.map