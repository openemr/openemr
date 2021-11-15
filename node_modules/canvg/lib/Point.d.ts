export default class Point {
    x: number;
    y: number;
    static parse(point: string, defaultValue?: number): Point;
    static parseScale(scale: string, defaultValue?: number): Point;
    static parsePath(path: string): Point[];
    constructor(x: number, y: number);
    angleTo(point: Point): number;
    applyTransform(transform: number[]): void;
}
//# sourceMappingURL=Point.d.ts.map