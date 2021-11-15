import { RenderingContext2D } from '../types';
import Document from '../Document';
import Point from '../Point';
import Property from '../Property';
import { ITransform } from './types';
export default class Matrix implements ITransform {
    type: string;
    protected matrix: number[];
    private readonly originX;
    private readonly originY;
    constructor(_: Document, matrix: string, transformOrigin: [Property<string>, Property<string>]);
    apply(ctx: RenderingContext2D): void;
    unapply(ctx: RenderingContext2D): void;
    applyToPoint(point: Point): void;
}
//# sourceMappingURL=Matrix.d.ts.map