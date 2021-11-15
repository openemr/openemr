import { Util } from './Util.js';
import { Layer } from './Layer.js';
import { _registerNode } from './Global.js';
export class FastLayer extends Layer {
    constructor(attrs) {
        super(attrs);
        this.listening(false);
        Util.warn('Konva.Fast layer is deprecated. Please use "new Konva.Layer({ listening: false })" instead.');
    }
}
FastLayer.prototype.nodeType = 'FastLayer';
_registerNode(FastLayer);
