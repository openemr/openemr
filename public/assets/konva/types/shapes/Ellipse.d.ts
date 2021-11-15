import { Shape, ShapeConfig } from '../Shape';
import { GetSet, Vector2d } from '../types';
export interface EllipseConfig extends ShapeConfig {
    radiusX: number;
    radiusY: number;
}
export declare class Ellipse extends Shape<EllipseConfig> {
    _sceneFunc(context: any): void;
    getWidth(): number;
    getHeight(): number;
    setWidth(width: any): void;
    setHeight(height: any): void;
    radius: GetSet<Vector2d, this>;
    radiusX: GetSet<number, this>;
    radiusY: GetSet<number, this>;
}
