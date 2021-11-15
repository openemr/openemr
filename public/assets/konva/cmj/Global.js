"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports._registerNode = exports.Konva = exports.glob = void 0;
var PI_OVER_180 = Math.PI / 180;
function detectBrowser() {
    return (typeof window !== 'undefined' &&
        ({}.toString.call(window) === '[object Window]' ||
            {}.toString.call(window) === '[object global]'));
}
exports.glob = typeof global !== 'undefined'
    ? global
    : typeof window !== 'undefined'
        ? window
        : typeof WorkerGlobalScope !== 'undefined'
            ? self
            : {};
exports.Konva = {
    _global: exports.glob,
    version: '@@version',
    isBrowser: detectBrowser(),
    isUnminified: /param/.test(function (param) { }.toString()),
    dblClickWindow: 400,
    getAngle(angle) {
        return exports.Konva.angleDeg ? angle * PI_OVER_180 : angle;
    },
    enableTrace: false,
    pointerEventsEnabled: true,
    autoDrawEnabled: true,
    hitOnDragEnabled: false,
    capturePointerEventsEnabled: false,
    _mouseListenClick: false,
    _touchListenClick: false,
    _pointerListenClick: false,
    _mouseInDblClickWindow: false,
    _touchInDblClickWindow: false,
    _pointerInDblClickWindow: false,
    _mouseDblClickPointerId: null,
    _touchDblClickPointerId: null,
    _pointerDblClickPointerId: null,
    pixelRatio: (typeof window !== 'undefined' && window.devicePixelRatio) || 1,
    dragDistance: 3,
    angleDeg: true,
    showWarnings: true,
    dragButtons: [0, 1],
    isDragging() {
        return exports.Konva['DD'].isDragging;
    },
    isDragReady() {
        return !!exports.Konva['DD'].node;
    },
    document: exports.glob.document,
    _injectGlobal(Konva) {
        exports.glob.Konva = Konva;
    },
};
const _registerNode = (NodeClass) => {
    exports.Konva[NodeClass.prototype.getClassName()] = NodeClass;
};
exports._registerNode = _registerNode;
exports.Konva._injectGlobal(exports.Konva);
