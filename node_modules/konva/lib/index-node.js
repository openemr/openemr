import { Konva } from './_FullInternals.js';
import * as Canvas from 'canvas';
const canvas = Canvas['default'] || Canvas;
const isNode = typeof global.document === 'undefined';
if (isNode) {
    Konva.Util['createCanvasElement'] = () => {
        const node = canvas.createCanvas(300, 300);
        if (!node['style']) {
            node['style'] = {};
        }
        return node;
    };
    Konva.Util.createImageElement = () => {
        const node = new canvas.Image();
        return node;
    };
}
export default Konva;
