import { Collection } from './Util';
import { Container, ContainerConfig } from './Container';
import { SceneCanvas, HitCanvas } from './Canvas';
import { GetSet, Vector2d } from './types';
import { Shape } from './Shape';
import { Layer } from './Layer';
export interface StageConfig extends ContainerConfig {
    container: HTMLDivElement | string;
}
export declare const stages: Stage[];
export declare class Stage extends Container<Layer> {
    content: HTMLDivElement;
    pointerPos: Vector2d | null;
    _pointerPositions: (Vector2d & {
        id?: number;
    })[];
    _changedPointerPositions: (Vector2d & {
        id?: number;
    })[];
    bufferCanvas: SceneCanvas;
    bufferHitCanvas: HitCanvas;
    targetShape: Shape;
    clickStartShape: Shape;
    clickEndShape: Shape;
    tapStartShape: Shape;
    tapEndShape: Shape;
    dblTimeout: any;
    constructor(config: StageConfig);
    _validateAdd(child: any): void;
    _checkVisibility(): void;
    setContainer(container: any): this;
    shouldDrawHit(): boolean;
    clear(): this;
    clone(obj: any): any;
    destroy(): this;
    getPointerPosition(): Vector2d | null;
    _getPointerById(id?: number): Vector2d & {
        id?: number;
    };
    getPointersPositions(): (Vector2d & {
        id?: number;
    })[];
    getStage(): this;
    getContent(): HTMLDivElement;
    _toKonvaCanvas(config: any): SceneCanvas;
    getIntersection(pos: Vector2d | null, selector?: string): Shape | null;
    _resizeDOM(): void;
    add(layer: Layer): this;
    getParent(): any;
    getLayer(): any;
    hasPointerCapture(pointerId: number): boolean;
    setPointerCapture(pointerId: number): void;
    releaseCapture(pointerId: number): void;
    getLayers(): Collection<import("./Node").Node<import("./Node").NodeConfig>>;
    _bindContentEvents(): void;
    _mouseenter(evt: any): void;
    _mouseover(evt: any): void;
    _mouseleave(evt: any): void;
    _mousemove(evt: any): void;
    _mousedown(evt: any): void;
    _mouseup(evt: any): void;
    _contextmenu(evt: any): void;
    _touchstart(evt: any): void;
    _touchmove(evt: any): void;
    _touchend(evt: any): void;
    _wheel(evt: any): void;
    _pointerdown(evt: PointerEvent): void;
    _pointermove(evt: PointerEvent): void;
    _pointerup(evt: PointerEvent): void;
    _pointercancel(evt: PointerEvent): void;
    _lostpointercapture(evt: PointerEvent): void;
    setPointersPositions(evt: any): void;
    _setPointerPosition(evt: any): void;
    _getContentPosition(): {
        top: number;
        left: number;
        scaleX: number;
        scaleY: number;
    };
    _buildDOM(): void;
    cache(): this;
    clearCache(): this;
    batchDraw(): this;
    container: GetSet<HTMLDivElement, this>;
}
