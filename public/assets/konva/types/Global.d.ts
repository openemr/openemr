export declare const _parseUA: (userAgent: any) => {
    browser: any;
    version: any;
    isIE: number | boolean;
    mobile: boolean;
    ieMobile: boolean;
};
export declare const glob: any;
export declare const Konva: {
    _global: any;
    version: string;
    isBrowser: boolean;
    isUnminified: boolean;
    dblClickWindow: number;
    getAngle(angle: any): any;
    enableTrace: boolean;
    _pointerEventsEnabled: boolean;
    hitOnDragEnabled: boolean;
    captureTouchEventsEnabled: boolean;
    listenClickTap: boolean;
    inDblClickWindow: boolean;
    pixelRatio: any;
    dragDistance: number;
    angleDeg: boolean;
    showWarnings: boolean;
    dragButtons: number[];
    isDragging(): any;
    isDragReady(): boolean;
    UA: {
        browser: any;
        version: any;
        isIE: number | boolean;
        mobile: boolean;
        ieMobile: boolean;
    };
    document: any;
    _injectGlobal(Konva: any): void;
    _parseUA: (userAgent: any) => {
        browser: any;
        version: any;
        isIE: number | boolean;
        mobile: boolean;
        ieMobile: boolean;
    };
};
export declare const _NODES_REGISTRY: {};
export declare const _registerNode: (NodeClass: any) => void;
