import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export interface ArcConfig extends ShapeConfig {
    angle: number;
    innerRadius: number;
    outerRadius: number;
    clockwise?: boolean;
}
export declare class Arc extends Shape<ArcConfig> {
    _sceneFunc(context: any): void;
    getWidth(): number;
    getHeight(): number;
    setWidth(width: any): void;
    setHeight(height: any): void;
    innerRadius: GetSet<number, this>;
    outerRadius: GetSet<number, this>;
    angle: GetSet<number, this>;
    clockwise: GetSet<boolean, this>;
}
