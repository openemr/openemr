import { RenderingContext2D } from './types';
import BoundingBox from './BoundingBox';
import Screen from './Screen';
import { Element } from './Document';
interface IEventTarget {
    onClick?(): void;
    onMouseMove?(): void;
}
export interface IEvent {
    type: string;
    x: number;
    y: number;
    run(eventTarget: IEventTarget): void;
}
export default class Mouse {
    private readonly screen;
    private working;
    private events;
    private eventElements;
    constructor(screen: Screen);
    isWorking(): boolean;
    start(): void;
    stop(): void;
    hasEvents(): boolean;
    runEvents(): void;
    checkPath(element: Element, ctx: RenderingContext2D): void;
    checkBoundingBox(element: Element, boundingBox: BoundingBox): void;
    private mapXY;
    private onClick;
    private onMouseMove;
}
export {};
//# sourceMappingURL=Mouse.d.ts.map