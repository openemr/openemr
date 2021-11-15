import Document from './Document';
import Element from './Element';
export default class FontFaceElement extends Element {
    type: string;
    readonly ascent: number;
    readonly descent: number;
    readonly unitsPerEm: number;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
}
//# sourceMappingURL=FontFaceElement.d.ts.map