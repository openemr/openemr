import { Shape, ShapeConfig } from '../Shape';
import { Group } from '../Group';
import { ContainerConfig } from '../Container';
import { GetSet } from '../types';
export interface LabelConfig extends ContainerConfig {
}
export declare class Label extends Group {
    constructor(config: any);
    getText(): import("../Node").Node<import("../Node").NodeConfig>;
    getTag(): Tag;
    _addListeners(text: any): void;
    getWidth(): number;
    getHeight(): number;
    _sync(): void;
}
export interface TagConfig extends ShapeConfig {
    pointerDirection?: string;
    pointerWidth?: number;
    pointerHeight?: number;
    cornerRadius?: number;
}
export declare class Tag extends Shape<TagConfig> {
    _sceneFunc(context: any): void;
    getSelfRect(): {
        x: number;
        y: number;
        width: number;
        height: number;
    };
    pointerDirection: GetSet<'left' | 'top' | 'right' | 'bottom', this>;
    pointerWidth: GetSet<number, this>;
    pointerHeight: GetSet<number, this>;
    cornerRadius: GetSet<number, this>;
}
