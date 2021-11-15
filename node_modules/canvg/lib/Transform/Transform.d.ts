import { RenderingContext2D } from '../types';
import Property from '../Property';
import Point from '../Point';
import Document, { Element } from '../Document';
import { ITransform } from './types';
import Translate from './Translate';
import Rotate from './Rotate';
import Scale from './Scale';
import Matrix from './Matrix';
import Skew from './Skew';
import SkewX from './SkewX';
import SkewY from './SkewY';
export { Translate, Rotate, Scale, Matrix, Skew, SkewX, SkewY };
interface ITransformConstructor {
    prototype: ITransform;
    new (document: Document, value: string, transformOrigin?: readonly [Property<string>, Property<string>]): ITransform;
}
export default class Transform {
    private readonly document;
    static fromElement(document: Document, element: Element): Transform;
    static transformTypes: Record<string, ITransformConstructor>;
    private readonly transforms;
    constructor(document: Document, transform: string, transformOrigin?: readonly [Property<string>, Property<string>]);
    apply(ctx: RenderingContext2D): void;
    unapply(ctx: RenderingContext2D): void;
    applyToPoint(point: Point): void;
}
//# sourceMappingURL=Transform.d.ts.map