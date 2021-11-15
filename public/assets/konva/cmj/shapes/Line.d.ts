import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
import { Context } from '../Context';
export interface LineConfig extends ShapeConfig {
    points?: number[];
    tension?: number;
    closed?: boolean;
    bezier?: boolean;
}
export declare class Line<Config extends LineConfig = LineConfig> extends Shape<Config> {
    constructor(config?: Config);
    _sceneFunc(context: Context): void;
    getTensionPoints(): any;
    _getTensionPoints(): any[];
    _getTensionPointsClosed(): any[];
    getWidth(): number;
    getHeight(): number;
    getSelfRect(): {
        x: number;
        y: number;
        width: number;
        height: number;
    };
    closed: GetSet<boolean, this>;
    bezier: GetSet<boolean, this>;
    tension: GetSet<number, this>;
    points: GetSet<number[], this>;
}
