import Document from './Document';
import PathElement from './PathElement';
export default class GlyphElement extends PathElement {
    type: string;
    readonly horizAdvX: number;
    readonly unicode: string;
    readonly arabicForm: string;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
}
//# sourceMappingURL=GlyphElement.d.ts.map