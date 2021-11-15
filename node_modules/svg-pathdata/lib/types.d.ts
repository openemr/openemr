import { SVGPathData } from "./SVGPathData";
export declare type CommandM = {
    relative: boolean;
    type: typeof SVGPathData.MOVE_TO;
    x: number;
    y: number;
};
export declare type CommandL = {
    relative: boolean;
    type: typeof SVGPathData.LINE_TO;
    x: number;
    y: number;
};
export declare type CommandH = {
    relative: boolean;
    type: typeof SVGPathData.HORIZ_LINE_TO;
    x: number;
};
export declare type CommandV = {
    relative: boolean;
    type: typeof SVGPathData.VERT_LINE_TO;
    y: number;
};
export declare type CommandZ = {
    type: typeof SVGPathData.CLOSE_PATH;
};
export declare type CommandQ = {
    relative: boolean;
    type: typeof SVGPathData.QUAD_TO;
    x1: number;
    y1: number;
    x: number;
    y: number;
};
export declare type CommandT = {
    relative: boolean;
    type: typeof SVGPathData.SMOOTH_QUAD_TO;
    x: number;
    y: number;
};
export declare type CommandC = {
    relative: boolean;
    type: typeof SVGPathData.CURVE_TO;
    x1: number;
    y1: number;
    x2: number;
    y2: number;
    x: number;
    y: number;
};
export declare type CommandS = {
    relative: boolean;
    type: typeof SVGPathData.SMOOTH_CURVE_TO;
    x2: number;
    y2: number;
    x: number;
    y: number;
};
export declare type CommandA = {
    relative: boolean;
    type: typeof SVGPathData.ARC;
    rX: number;
    rY: number;
    xRot: number;
    sweepFlag: 0 | 1;
    lArcFlag: 0 | 1;
    x: number;
    y: number;
    cX?: number;
    cY?: number;
    phi1?: number;
    phi2?: number;
};
export declare type SVGCommand = CommandM | CommandL | CommandH | CommandV | CommandZ | CommandQ | CommandT | CommandC | CommandS | CommandA;
export declare type TransformFunction = (input: SVGCommand) => SVGCommand | SVGCommand[];
