import Document from './Document';
import TextElement from './TextElement';
export default class TSpanElement extends TextElement {
    type: string;
    protected readonly text: string;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    getText(): string;
}
//# sourceMappingURL=TSpanElement.d.ts.map