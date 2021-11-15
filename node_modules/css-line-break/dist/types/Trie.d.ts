export declare type int = number;
/** Shift size for getting the index-2 table offset. */
export declare const UTRIE2_SHIFT_2 = 5;
/** Shift size for getting the index-1 table offset. */
export declare const UTRIE2_SHIFT_1: number;
/**
 * Shift size for shifting left the index array values.
 * Increases possible data size with 16-bit index values at the cost
 * of compactability.
 * This requires data blocks to be aligned by UTRIE2_DATA_GRANULARITY.
 */
export declare const UTRIE2_INDEX_SHIFT = 2;
/**
 * Difference between the two shift sizes,
 * for getting an index-1 offset from an index-2 offset. 6=11-5
 */
export declare const UTRIE2_SHIFT_1_2: number;
/**
 * The part of the index-2 table for U+D800..U+DBFF stores values for
 * lead surrogate code _units_ not code _points_.
 * Values for lead surrogate code _points_ are indexed with this portion of the table.
 * Length=32=0x20=0x400>>UTRIE2_SHIFT_2. (There are 1024=0x400 lead surrogates.)
 */
export declare const UTRIE2_LSCP_INDEX_2_OFFSET: number;
/** Number of entries in a data block. 32=0x20 */
export declare const UTRIE2_DATA_BLOCK_LENGTH: number;
/** Mask for getting the lower bits for the in-data-block offset. */
export declare const UTRIE2_DATA_MASK: number;
export declare const UTRIE2_LSCP_INDEX_2_LENGTH: number;
/** Count the lengths of both BMP pieces. 2080=0x820 */
export declare const UTRIE2_INDEX_2_BMP_LENGTH: number;
/**
 * The 2-byte UTF-8 version of the index-2 table follows at offset 2080=0x820.
 * Length 32=0x20 for lead bytes C0..DF, regardless of UTRIE2_SHIFT_2.
 */
export declare const UTRIE2_UTF8_2B_INDEX_2_OFFSET: number;
export declare const UTRIE2_UTF8_2B_INDEX_2_LENGTH: number;
/**
 * The index-1 table, only used for supplementary code points, at offset 2112=0x840.
 * Variable length, for code points up to highStart, where the last single-value range starts.
 * Maximum length 512=0x200=0x100000>>UTRIE2_SHIFT_1.
 * (For 0x100000 supplementary code points U+10000..U+10ffff.)
 *
 * The part of the index-2 table for supplementary code points starts
 * after this index-1 table.
 *
 * Both the index-1 table and the following part of the index-2 table
 * are omitted completely if there is only BMP data.
 */
export declare const UTRIE2_INDEX_1_OFFSET: number;
/**
 * Number of index-1 entries for the BMP. 32=0x20
 * This part of the index-1 table is omitted from the serialized form.
 */
export declare const UTRIE2_OMITTED_BMP_INDEX_1_LENGTH: number;
/** Number of entries in an index-2 block. 64=0x40 */
export declare const UTRIE2_INDEX_2_BLOCK_LENGTH: number;
/** Mask for getting the lower bits for the in-index-2-block offset. */
export declare const UTRIE2_INDEX_2_MASK: number;
export declare const createTrieFromBase64: (base64: string) => Trie;
export declare class Trie {
    initialValue: int;
    errorValue: int;
    highStart: int;
    highValueIndex: int;
    index: Uint16Array | number[];
    data: Uint32Array | Uint16Array | number[];
    constructor(initialValue: int, errorValue: int, highStart: int, highValueIndex: int, index: Uint16Array | number[], data: Uint32Array | Uint16Array | number[]);
    /**
     * Get the value for a code point as stored in the Trie.
     *
     * @param codePoint the code point
     * @return the value
     */
    get(codePoint: number): number;
}
