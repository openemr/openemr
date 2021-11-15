import { Node, NodeConfig } from './Node';
import { GetSet, IRect } from './types';
import { HitCanvas, SceneCanvas } from './Canvas';
import { SceneContext } from './Context';
export interface ContainerConfig extends NodeConfig {
    clearBeforeDraw?: boolean;
    clipFunc?: (ctx: SceneContext) => void;
    clipX?: number;
    clipY?: number;
    clipWidth?: number;
    clipHeight?: number;
}
export declare abstract class Container<ChildType extends Node = Node> extends Node<ContainerConfig> {
    children: Array<ChildType> | undefined;
    getChildren(filterFunc?: (item: Node) => boolean): ChildType[];
    hasChildren(): boolean;
    removeChildren(): this;
    destroyChildren(): this;
    abstract _validateAdd(node: Node): void;
    add(...children: ChildType[]): this;
    destroy(): this;
    find<ChildNode extends Node = Node>(selector: any): Array<ChildNode>;
    findOne<ChildNode extends Node = Node>(selector: string | Function): ChildNode;
    _generalFind<ChildNode extends Node = Node>(selector: string | Function, findOne: boolean): ChildNode[];
    private _descendants;
    toObject(): any;
    isAncestorOf(node: Node): boolean;
    clone(obj?: any): this;
    getAllIntersections(pos: any): any[];
    _clearSelfAndDescendantCache(attr?: string): void;
    _setChildrenIndices(): void;
    drawScene(can?: SceneCanvas, top?: Node): this;
    drawHit(can?: HitCanvas, top?: Node): this;
    _drawChildren(drawMethod: any, canvas: any, top: any): void;
    getClientRect(config?: {
        skipTransform?: boolean;
        skipShadow?: boolean;
        skipStroke?: boolean;
        relativeTo?: Container<Node>;
    }): IRect;
    clip: GetSet<IRect, this>;
    clipX: GetSet<number, this>;
    clipY: GetSet<number, this>;
    clipWidth: GetSet<number, this>;
    clipHeight: GetSet<number, this>;
    clipFunc: GetSet<(ctx: CanvasRenderingContext2D, shape: Container<ChildType>) => void, this>;
}
