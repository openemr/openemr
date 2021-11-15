export interface IParserOptions {
    /**
     * WHATWG-compatible `fetch` function.
     */
    fetch?: typeof fetch;
    /**
     * XML/HTML parser from string into DOM Document.
     */
    DOMParser?: typeof DOMParser;
}
export default class Parser {
    private readonly fetch;
    private readonly DOMParser;
    constructor({ fetch, DOMParser }?: IParserOptions);
    parse(resource: string): Promise<Document>;
    parseFromString(xml: string): Document;
    private checkDocument;
    load(url: string): Promise<Document>;
}
//# sourceMappingURL=Parser.d.ts.map