import Document from './Document';
import Element from './Element';
export default class StopElement extends Element {
    type: string;
    readonly offset: number;
    readonly color: string;
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
}
//# sourceMappingURL=StopElement.d.ts.map