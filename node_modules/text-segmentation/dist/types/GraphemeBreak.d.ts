export declare const classes: {
    [key: string]: number;
};
export declare const toCodePoints: (str: string) => number[];
export declare const fromCodePoint: (...codePoints: number[]) => string;
export declare const UnicodeTrie: import("utrie").Trie;
export declare const BREAK_NOT_ALLOWED = "\u00D7";
export declare const BREAK_ALLOWED = "\u00F7";
export declare type BREAK_OPPORTUNITIES = typeof BREAK_NOT_ALLOWED | typeof BREAK_ALLOWED;
export declare const codePointToClass: (codePoint: number) => number;
export declare const graphemeBreakAtIndex: (codePoints: number[], index: number) => BREAK_OPPORTUNITIES;
export declare const GraphemeBreaker: (str: string) => {
    next: () => {
        done: boolean;
        value: null;
    } | {
        value: string;
        done: boolean;
    };
};
export declare const splitGraphemes: (str: string) => string[];
