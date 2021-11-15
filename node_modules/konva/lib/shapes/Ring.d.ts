import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export interface RingConfig extends ShapeConfig {
    innerRadius: number;
    outerRadius: number;
    clockwise?: boolean;
}
export declare class Ring extends Shape<RingConfig> {
    _sceneFunc(context: any): void;
    getWidth(): number;
    getHeight(): number;
    setWidth(width: any): void;
    setHeight(height: any): void;
    outerRadius: GetSet<number, this>;
    innerRadius: GetSet<number, this>;
}
