export default class BoundingBox {
    x1: number;
    y1: number;
    x2: number;
    y2: number;
    constructor(x1?: number, y1?: number, x2?: number, y2?: number);
    get x(): number;
    get y(): number;
    get width(): number;
    get height(): number;
    addPoint(x: number, y: number): void;
    addX(x: number): void;
    addY(y: number): void;
    addBoundingBox(boundingBox: BoundingBox): void;
    private sumCubic;
    private bezierCurveAdd;
    addBezierCurve(p0x: number, p0y: number, p1x: number, p1y: number, p2x: number, p2y: number, p3x: number, p3y: number): void;
    addQuadraticCurve(p0x: number, p0y: number, p1x: number, p1y: number, p2x: number, p2y: number): void;
    isPointInBox(x: number, y: number): boolean;
}
//# sourceMappingURL=BoundingBox.d.ts.map