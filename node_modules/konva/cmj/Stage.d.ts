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
        id: number;
    })[];
    bufferCanvas: SceneCanvas;
    bufferHitCanvas: HitCanvas;
    _mouseTargetShape: Shape;
    _touchTargetShape: Shape;
    _pointerTargetShape: Shape;
    _mouseClickStartShape: Shape;
    _touchClickStartShape: Shape;
    _pointerClickStartShape: Shape;
    _mouseClickEndShape: Shape;
    _touchClickEndShape: Shape;
    _pointerClickEndShape: Shape;
    _mouseDblTimeout: any;
    _touchDblTimeout: any;
    _pointerDblTimeout: any;
    constructor(config: StageConfig);
    _validateAdd(child: any): void;
    _checkVisibility(): void;
    setContainer(container: any): this;
    shouldDrawHit(): boolean;
    clear(): this;
    clone(obj?: any): any;
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
    getIntersection(pos: Vector2d): Shape<import("./Shape").ShapeConfig>;
    _resizeDOM(): void;
    add(layer: Layer, ...rest: any[]): this;
    getParent(): any;
    getLayer(): any;
    hasPointerCapture(pointerId: number): boolean;
    setPointerCapture(pointerId: number): void;
    releaseCapture(pointerId: number): void;
    getLayers(): Layer[];
    _bindContentEvents(): void;
    _pointerenter(evt: any): void;
    _pointerover(evt: any): void;
    _getTargetShape(evenType: any): Shape<import("./Shape").ShapeConfig>;
    _pointerleave(evt: any): void;
    _pointerdown(evt: TouchEvent | MouseEvent | PointerEvent): void;
    _pointermove(evt: TouchEvent | MouseEvent | PointerEvent): void;
    _pointerup(evt: any): void;
    _contextmenu(evt: any): void;
    _wheel(evt: any): void;
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
