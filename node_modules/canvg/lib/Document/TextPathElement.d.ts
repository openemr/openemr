import { RenderingContext2D } from '../types';
import PathParser, { CommandType } from '../PathParser';
import Document from './Document';
import TextElement from './TextElement';
import PathElement from './PathElement';
export interface IPoint {
    x: number;
    y: number;
}
export interface IPathCommand {
    type: CommandType;
    points: number[];
    start?: IPoint;
    pathLength: number;
}
interface ICachedPoint extends IPoint {
    distance: number;
}
interface IGlyphInfo {
    text: string;
    rotation: number;
    p0: ICachedPoint;
    p1: ICachedPoint;
}
export default class TextPathElement extends TextElement {
    type: string;
    protected textWidth: number;
    protected textHeight: number;
    protected pathLength: number;
    protected glyphInfo: IGlyphInfo[];
    protected readonly text: string;
    protected readonly dataArray: IPathCommand[];
    private letterSpacingCache;
    private equidistantCache;
    private readonly measuresCache;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    getText(): string;
    path(ctx: RenderingContext2D): void;
    renderChildren(ctx: RenderingContext2D): void;
    protected getLetterSpacingAt(idx?: number): number;
    protected findSegmentToFitChar(ctx: RenderingContext2D, anchor: string, textFullWidth: number, fullPathWidth: number, spacesNumber: number, inputOffset: number, dy: number, c: string, charI: number): {
        offset: number;
        segment: {
            p0: ICachedPoint;
            p1: ICachedPoint;
        };
        rotation: number;
    };
    protected measureText(ctx: RenderingContext2D, text?: string): number;
    protected setTextData(ctx: RenderingContext2D): void;
    protected parsePathData(path: PathElement): IPathCommand[];
    protected pathM(pathParser: PathParser, points: number[]): void;
    protected pathL(pathParser: PathParser, points: number[]): 16;
    protected pathH(pathParser: PathParser, points: number[]): 16;
    protected pathV(pathParser: PathParser, points: number[]): 16;
    protected pathC(pathParser: PathParser, points: number[]): void;
    protected pathS(pathParser: PathParser, points: number[]): 32;
    protected pathQ(pathParser: PathParser, points: number[]): void;
    protected pathT(pathParser: PathParser, points: number[]): 128;
    protected pathA(pathParser: PathParser): number[];
    protected calcLength(x: number, y: number, commandType: CommandType, points: number[]): number;
    protected getPointOnLine(dist: number, p1x: number, p1y: number, p2x: number, p2y: number, fromX?: number, fromY?: number): IPoint;
    protected getPointOnPath(distance: number): IPoint;
    protected getLineLength(x1: number, y1: number, x2: number, y2: number): number;
    protected getPathLength(): number;
    protected getPointOnCubicBezier(pct: number, p1x: number, p1y: number, p2x: number, p2y: number, p3x: number, p3y: number, p4x: number, p4y: number): IPoint;
    protected getPointOnQuadraticBezier(pct: number, p1x: number, p1y: number, p2x: number, p2y: number, p3x: number, p3y: number): IPoint;
    protected getPointOnEllipticalArc(cx: number, cy: number, rx: number, ry: number, theta: number, psi: number): IPoint;
    protected buildEquidistantCache(inputStep: number, inputPrecision: number): void;
    protected getEquidistantPointOnPath(targetDistance: number, step?: number, precision?: number): ICachedPoint;
}
export {};
//# sourceMappingURL=TextPathElement.d.ts.map