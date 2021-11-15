import Document from './Document';
export default class SVGFontLoader {
    private readonly document;
    loaded: boolean;
    constructor(document: Document);
    load(fontFamily: string, url: string): Promise<void>;
}
//# sourceMappingURL=SVGFontLoader.d.ts.map