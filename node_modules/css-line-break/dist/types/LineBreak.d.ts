export declare const LETTER_NUMBER_MODIFIER = 50;
export declare const classes: {
    [key: string]: number;
};
export declare const BREAK_MANDATORY = "!";
export declare const BREAK_NOT_ALLOWED = "\u00D7";
export declare const BREAK_ALLOWED = "\u00F7";
export declare const UnicodeTrie: import("./Trie").Trie;
export declare const codePointsToCharacterClasses: (codePoints: number[], lineBreak?: string) => [number[], number[], boolean[]];
export declare type BREAK_OPPORTUNITIES = typeof BREAK_NOT_ALLOWED | typeof BREAK_ALLOWED | typeof BREAK_MANDATORY;
export declare const lineBreakAtIndex: (codePoints: number[], index: number) => BREAK_OPPORTUNITIES;
export declare type LINE_BREAK = 'auto' | 'normal' | 'strict';
export declare type WORD_BREAK = 'normal' | 'break-all' | 'break-word' | 'keep-all';
interface IOptions {
    lineBreak?: LINE_BREAK;
    wordBreak?: WORD_BREAK;
}
export declare const inlineBreakOpportunities: (str: string, options?: IOptions | undefined) => string;
declare class Break {
    private readonly codePoints;
    readonly required: boolean;
    readonly start: number;
    readonly end: number;
    constructor(codePoints: number[], lineBreak: string, start: number, end: number);
    slice(): string;
}
export declare type LineBreak = {
    done: true;
    value: null;
} | {
    done: false;
    value: Break;
};
interface ILineBreakIterator {
    next: () => LineBreak;
}
export declare const LineBreaker: (str: string, options?: IOptions | undefined) => ILineBreakIterator;
export {};
