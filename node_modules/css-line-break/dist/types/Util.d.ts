export declare const toCodePoints: (str: string) => number[];
export declare const fromCodePoint: (...codePoints: number[]) => string;
export declare const decode: (base64: string) => ArrayBuffer | number[];
export declare const polyUint16Array: (buffer: number[]) => number[];
export declare const polyUint32Array: (buffer: number[]) => number[];
