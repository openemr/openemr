import { Collection } from './Util';
import { Node, NodeConfig } from './Node';
import { GetSet, IRect } from './types';
import { HitCanvas, SceneCanvas } from './Canvas';
export interface ContainerConfig extends NodeConfig {
    clearBeforeDraw?: boolean;
    clipFunc?: (ctx: CanvasRenderingContext2D) => void;
    clipX?: number;
    clipY?: number;
    clipWidth?: number;
    clipHeight?: number;
}
export declare abstract class Container<ChildType extends Node> extends Node<ContainerConfig> {
    children: Collection<ChildType>;
    getChildren(filterFunc?: (item: Node) => boolean): Collection<Node<NodeConfig>>;
    hasChildren(): boolean;
    removeChildren(): this;
    destroyChildren(): this;
    abstract _validateAdd(node: Node): void;
    add(...children: ChildType[]): this;
    destroy(): this;
    find<ChildNode extends Node = Node>(selector: any): Collection<ChildNode>;
    get(selector: any): Collection<Node<NodeConfig>>;
    findOne<ChildNode extends Node = Node>(selector: string | Function): ChildNode;
    _generalFind<ChildNode extends Node = Node>(selector: string | Function, findOne: boolean): Collection<ChildNode>;
    private _descendants;
    toObject(): any;
    isAncestorOf(node: Node): boolean;
    clone(obj?: any): any;
    getAllIntersections(pos: any): any[];
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
