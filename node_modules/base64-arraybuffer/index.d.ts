declare module 'base64-arraybuffer' {
    export function encode(buffer: ArrayBuffer): string;
    export function decode(str: string): ArrayBuffer;
}
