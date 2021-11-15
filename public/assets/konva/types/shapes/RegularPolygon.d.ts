import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export interface RegularPolygonConfig extends ShapeConfig {
    sides: number;
    radius: number;
}
export declare class RegularPolygon extends Shape<RegularPolygonConfig> {
    _sceneFunc(context: any): void;
    _getPoints(): any[];
    getSelfRect(): {
        x: any;
        y: any;
        width: number;
        height: number;
    };
    getWidth(): number;
    getHeight(): number;
    setWidth(width: any): void;
    setHeight(height: any): void;
    radius: GetSet<number, this>;
    sides: GetSet<number, this>;
}
