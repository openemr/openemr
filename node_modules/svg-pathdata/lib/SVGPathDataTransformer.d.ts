import { SVGCommand, TransformFunction } from "./types";
export declare namespace SVGPathDataTransformer {
    function ROUND(roundVal?: number): (command: any) => any;
    function TO_ABS(): (command: any) => any;
    function TO_REL(): (command: any) => any;
    function NORMALIZE_HVZ(normalizeZ?: boolean, normalizeH?: boolean, normalizeV?: boolean): (command: any) => any;
    function NORMALIZE_ST(): (command: any) => any;
    function QT_TO_C(): (command: any) => any;
    function INFO(f: (command: any, prevXAbs: number, prevYAbs: number, pathStartXAbs: number, pathStartYAbs: number) => any | any[]): (command: any) => any;
    function SANITIZE(EPS?: number): (command: any) => any;
    function MATRIX(a: number, b: number, c: number, d: number, e: number, f: number): (command: any) => any;
    function ROTATE(a: number, x?: number, y?: number): (command: any) => any;
    function TRANSLATE(dX: number, dY?: number): (command: any) => any;
    function SCALE(dX: number, dY?: number): (command: any) => any;
    function SKEW_X(a: number): (command: any) => any;
    function SKEW_Y(a: number): (command: any) => any;
    function X_AXIS_SYMMETRY(xOffset?: number): (command: any) => any;
    function Y_AXIS_SYMMETRY(yOffset?: number): (command: any) => any;
    function A_TO_C(): (command: any) => any;
    function ANNOTATE_ARCS(): (command: any) => any;
    function CLONE(): (c: SVGCommand) => SVGCommand;
    function CALCULATE_BOUNDS(): TransformFunction & {
        minX: number;
        maxX: number;
        minY: number;
        maxY: number;
    };
}
