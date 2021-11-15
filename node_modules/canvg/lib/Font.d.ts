export default class Font {
    static parse(font?: string, inherit?: string | Font): Font;
    static readonly styles = "normal|italic|oblique|inherit";
    static readonly variants = "normal|small-caps|inherit";
    static readonly weights = "normal|bold|bolder|lighter|100|200|300|400|500|600|700|800|900|inherit";
    readonly fontFamily: string;
    readonly fontSize: string;
    readonly fontStyle: string;
    readonly fontWeight: string;
    readonly fontVariant: string;
    constructor(fontStyle: string, fontVariant: string, fontWeight: string, fontSize: string, fontFamily: string, inherit?: string | Font);
    toString(): string;
}
//# sourceMappingURL=Font.d.ts.map