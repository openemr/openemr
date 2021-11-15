import { SVGCommand, CommandM, CommandL, CommandH, CommandV, CommandZ, CommandQ, CommandT, CommandC, CommandS, CommandA } from 'svg-pathdata/lib/types';
import { SVGPathData } from 'svg-pathdata';
import Point from './Point';
export declare type CommandType = SVGCommand['type'];
export declare type Command = {
    type: CommandType;
} & Omit<CommandM, 'type'> & Omit<CommandL, 'type'> & Omit<CommandH, 'type'> & Omit<CommandV, 'type'> & Omit<CommandZ, 'type'> & Omit<CommandQ, 'type'> & Omit<CommandT, 'type'> & Omit<CommandC, 'type'> & Omit<CommandS, 'type'> & Omit<CommandA, 'type'>;
export default class PathParser extends SVGPathData {
    control: Point;
    start: Point;
    current: Point;
    command: Command;
    readonly commands: Command[];
    private i;
    private previousCommand;
    private points;
    private angles;
    constructor(path: string);
    reset(): void;
    isEnd(): boolean;
    next(): Command;
    getPoint(xProp?: string, yProp?: string): Point;
    getAsControlPoint(xProp?: string, yProp?: string): Point;
    getAsCurrentPoint(xProp?: string, yProp?: string): Point;
    getReflectedControlPoint(): Point;
    makeAbsolute(point: Point): Point;
    addMarker(point: Point, from?: Point, priorTo?: Point): void;
    addMarkerAngle(point: Point, angle: number): void;
    getMarkerPoints(): Point[];
    getMarkerAngles(): number[];
}
//# sourceMappingURL=PathParser.d.ts.map