import { Trie, int } from './Trie';
export declare const BITS_16 = 16;
export declare const BITS_32 = 32;
export declare class TrieBuilder {
    index1: Uint32Array;
    index2: Uint32Array;
    map: Uint32Array;
    data: Uint32Array;
    dataCapacity: int;
    initialValue: int;
    errorValue: int;
    highStart: int;
    dataNullOffset: int;
    dataLength: int;
    index2NullOffset: int;
    index2Length: int;
    firstFreeBlock: int;
    isCompacted: boolean;
    constructor(initialValue?: int, errorValue?: int);
    /**
     * Set a value for a code point.
     *
     * @param c the code point
     * @param value the value
     */
    set(c: int, value: int): TrieBuilder;
    /**
     * Set a value in a range of code points [start..end].
     * All code points c with start<=c<=end will get the value if
     * overwrite is TRUE or if the old value is the initial value.
     *
     * @param start the first code point to get the value
     * @param end the last code point to get the value (inclusive)
     * @param value the value
     * @param overwrite flag for whether old non-initial values are to be overwritten
     */
    setRange(start: int, end: int, value: int, overwrite?: boolean): TrieBuilder;
    /**
     * Get the value for a code point as stored in the Trie2.
     *
     * @param codePoint the code point
     * @return the value
     */
    get(codePoint: int): int;
    _get(c: int, fromLSCP: boolean): int;
    freeze(valueBits?: 16 | 32): Trie;
    findHighStart(highValue: int): int;
    compactData(): void;
    findSameDataBlock(dataLength: int, otherBlock: int, blockLength: int): int;
    compactTrie(): void;
    compactIndex2(): void;
    findSameIndex2Block(index2Length: int, otherBlock: int): int;
    _set(c: int, forLSCP: boolean, value: int): this;
    writeBlock(block: int, value: int): void;
    isInNullBlock(c: int, forLSCP: boolean): boolean;
    fillBlock(block: int, start: int, limit: int, value: int, initialValue: int, overwrite: boolean): void;
    setIndex2Entry(i2: int, block: int): void;
    releaseDataBlock(block: int): void;
    getDataBlock(c: int, forLSCP: boolean): int;
    isWritableBlock(block: int): boolean;
    getIndex2Block(c: int, forLSCP: boolean): int;
    allocDataBlock(copyBlock: int): int;
    allocIndex2Block(): int;
}
export declare const serializeBase64: (trie: Trie) => [string, number];
