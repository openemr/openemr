import { Layer } from './Layer';
import { IFrame, AnimationFn } from './types';
export declare class Animation {
    func: AnimationFn;
    id: number;
    layers: Layer[];
    frame: IFrame;
    constructor(func: AnimationFn, layers?: any);
    setLayers(layers: any): this;
    getLayers(): Layer[];
    addLayer(layer: any): boolean;
    isRunning(): boolean;
    start(): this;
    stop(): this;
    _updateFrameObject(time: any): void;
    static animations: any[];
    static animIdCounter: number;
    static animRunning: boolean;
    static _addAnimation(anim: any): void;
    static _removeAnimation(anim: any): void;
    static _runFrames(): void;
    static _animationLoop(): void;
    static _handleAnimation(): void;
}
