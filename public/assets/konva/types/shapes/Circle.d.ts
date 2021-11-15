import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export interface CircleConfig extends ShapeConfig {
    radius: number;
}
export declare class Circle extends Shape<CircleConfig> {
    _sceneFunc(context: any): void;
    getWidth(): number;
    getHeight(): number;
    setWidth(width: any): void;
    setHeight(height: any): void;
    radius: GetSet<number, this>;
}
