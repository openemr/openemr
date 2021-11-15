import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export interface StarConfig extends ShapeConfig {
    numPoints: number;
    innerRadius: number;
    outerRadius: number;
}
export declare class Star extends Shape<StarConfig> {
    _sceneFunc(context: any): void;
    getWidth(): number;
    getHeight(): number;
    setWidth(width: any): void;
    setHeight(height: any): void;
    outerRadius: GetSet<number, this>;
    innerRadius: GetSet<number, this>;
    numPoints: GetSet<number, this>;
}
