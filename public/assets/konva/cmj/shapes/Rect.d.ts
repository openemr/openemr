import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export interface RectConfig extends ShapeConfig {
    cornerRadius?: number | number[];
}
export declare class Rect extends Shape<RectConfig> {
    _sceneFunc(context: any): void;
    cornerRadius: GetSet<number | number[], this>;
}
