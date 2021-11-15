import { Line, LineConfig } from './Line';
import { GetSet } from '../types';
export interface ArrowConfig extends LineConfig {
    points: number[];
    tension?: number;
    closed?: boolean;
    pointerLength?: number;
    pointerWidth?: number;
    pointerAtBeginning?: boolean;
}
export declare class Arrow extends Line<ArrowConfig> {
    _sceneFunc(ctx: any): void;
    getSelfRect(): {
        x: number;
        y: number;
        width: number;
        height: number;
    };
    pointerLength: GetSet<number, this>;
    pointerWidth: GetSet<number, this>;
    pointerAtBeginning: GetSet<boolean, this>;
}
