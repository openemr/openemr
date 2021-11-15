import { RenderingContext2D } from '../types';
import Point from '../Point';
export interface ITransform {
    type: string;
    apply(ctx: RenderingContext2D): void;
    unapply(ctx: RenderingContext2D): void;
    applyToPoint(point: Point): void;
}
//# sourceMappingURL=types.d.ts.map