export interface IViewPortSize {
    width: number;
    height: number;
}
export declare type Axis = 'x' | 'y';
export default class ViewPort {
    viewPorts: IViewPortSize[];
    clear(): void;
    setCurrent(width: number, height: number): void;
    removeCurrent(): void;
    getCurrent(): IViewPortSize;
    get width(): number;
    get height(): number;
    computeSize(d?: number | Axis): number;
}
//# sourceMappingURL=ViewPort.d.ts.map