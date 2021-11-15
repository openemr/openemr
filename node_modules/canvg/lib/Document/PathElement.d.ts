import { RenderingContext2D } from '../types';
import Point from '../Point';
import BoundingBox from '../BoundingBox';
import PathParser from '../PathParser';
import Document from './Document';
import RenderedElement from './RenderedElement';
export declare type Marker = [Point, number];
export default class PathElement extends RenderedElement {
    type: string;
    readonly pathParser: PathParser;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    path(ctx?: RenderingContext2D): BoundingBox;
    getBoundingBox(_?: RenderingContext2D): BoundingBox;
    getMarkers(): Marker[];
    renderChildren(ctx: RenderingContext2D): void;
    static pathM(pathParser: PathParser): {
        point: Point;
    };
    protected pathM(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathL(pathParser: PathParser): {
        current: Point;
        point: Point;
    };
    protected pathL(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathH(pathParser: PathParser): {
        current: Point;
        point: Point;
    };
    protected pathH(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathV(pathParser: PathParser): {
        current: Point;
        point: Point;
    };
    protected pathV(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathC(pathParser: PathParser): {
        current: Point;
        point: Point;
        controlPoint: Point;
        currentPoint: Point;
    };
    protected pathC(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathS(pathParser: PathParser): {
        current: Point;
        point: Point;
        controlPoint: Point;
        currentPoint: Point;
    };
    protected pathS(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathQ(pathParser: PathParser): {
        current: Point;
        controlPoint: Point;
        currentPoint: Point;
    };
    protected pathQ(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathT(pathParser: PathParser): {
        current: Point;
        controlPoint: Point;
        currentPoint: Point;
    };
    protected pathT(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathA(pathParser: PathParser): {
        currentPoint: Point;
        rX: number;
        rY: number;
        sweepFlag: 0 | 1;
        xAxisRotation: number;
        centp: Point;
        a1: number;
        ad: number;
    };
    protected pathA(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
    static pathZ(pathParser: PathParser): void;
    protected pathZ(ctx: RenderingContext2D, boundingBox: BoundingBox): void;
}
//# sourceMappingURL=PathElement.d.ts.map