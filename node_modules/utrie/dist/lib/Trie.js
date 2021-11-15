"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Trie = exports.createTrieFromBase64 = exports.UTRIE2_INDEX_2_MASK = exports.UTRIE2_INDEX_2_BLOCK_LENGTH = exports.UTRIE2_OMITTED_BMP_INDEX_1_LENGTH = exports.UTRIE2_INDEX_1_OFFSET = exports.UTRIE2_UTF8_2B_INDEX_2_LENGTH = exports.UTRIE2_UTF8_2B_INDEX_2_OFFSET = exports.UTRIE2_INDEX_2_BMP_LENGTH = exports.UTRIE2_LSCP_INDEX_2_LENGTH = exports.UTRIE2_DATA_MASK = exports.UTRIE2_DATA_BLOCK_LENGTH = exports.UTRIE2_LSCP_INDEX_2_OFFSET = exports.UTRIE2_SHIFT_1_2 = exports.UTRIE2_INDEX_SHIFT = exports.UTRIE2_SHIFT_1 = exports.UTRIE2_SHIFT_2 = void 0;
var Util_1 = require("./Util");
/** Shift size for getting the index-2 table offset. */
exports.UTRIE2_SHIFT_2 = 5;
/** Shift size for getting the index-1 table offset. */
exports.UTRIE2_SHIFT_1 = 6 + 5;
/**
 * Shift size for shifting left the index array values.
 * Increases possible data size with 16-bit index values at the cost
 * of compactability.
 * This requires data blocks to be aligned by UTRIE2_DATA_GRANULARITY.
 */
exports.UTRIE2_INDEX_SHIFT = 2;
/**
 * Difference between the two shift sizes,
 * for getting an index-1 offset from an index-2 offset. 6=11-5
 */
exports.UTRIE2_SHIFT_1_2 = exports.UTRIE2_SHIFT_1 - exports.UTRIE2_SHIFT_2;
/**
 * The part of the index-2 table for U+D800..U+DBFF stores values for
 * lead surrogate code _units_ not code _points_.
 * Values for lead surrogate code _points_ are indexed with this portion of the table.
 * Length=32=0x20=0x400>>UTRIE2_SHIFT_2. (There are 1024=0x400 lead surrogates.)
 */
exports.UTRIE2_LSCP_INDEX_2_OFFSET = 0x10000 >> exports.UTRIE2_SHIFT_2;
/** Number of entries in a data block. 32=0x20 */
exports.UTRIE2_DATA_BLOCK_LENGTH = 1 << exports.UTRIE2_SHIFT_2;
/** Mask for getting the lower bits for the in-data-block offset. */
exports.UTRIE2_DATA_MASK = exports.UTRIE2_DATA_BLOCK_LENGTH - 1;
exports.UTRIE2_LSCP_INDEX_2_LENGTH = 0x400 >> exports.UTRIE2_SHIFT_2;
/** Count the lengths of both BMP pieces. 2080=0x820 */
exports.UTRIE2_INDEX_2_BMP_LENGTH = exports.UTRIE2_LSCP_INDEX_2_OFFSET + exports.UTRIE2_LSCP_INDEX_2_LENGTH;
/**
 * The 2-byte UTF-8 version of the index-2 table follows at offset 2080=0x820.
 * Length 32=0x20 for lead bytes C0..DF, regardless of UTRIE2_SHIFT_2.
 */
exports.UTRIE2_UTF8_2B_INDEX_2_OFFSET = exports.UTRIE2_INDEX_2_BMP_LENGTH;
exports.UTRIE2_UTF8_2B_INDEX_2_LENGTH = 0x800 >> 6; /* U+0800 is the first code point after 2-byte UTF-8 */
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
exports.UTRIE2_INDEX_1_OFFSET = exports.UTRIE2_UTF8_2B_INDEX_2_OFFSET + exports.UTRIE2_UTF8_2B_INDEX_2_LENGTH;
/**
 * Number of index-1 entries for the BMP. 32=0x20
 * This part of the index-1 table is omitted from the serialized form.
 */
exports.UTRIE2_OMITTED_BMP_INDEX_1_LENGTH = 0x10000 >> exports.UTRIE2_SHIFT_1;
/** Number of entries in an index-2 block. 64=0x40 */
exports.UTRIE2_INDEX_2_BLOCK_LENGTH = 1 << exports.UTRIE2_SHIFT_1_2;
/** Mask for getting the lower bits for the in-index-2-block offset. */
exports.UTRIE2_INDEX_2_MASK = exports.UTRIE2_INDEX_2_BLOCK_LENGTH - 1;
var slice16 = function (view, start, end) {
    if (view.slice) {
        return view.slice(start, end);
    }
    return new Uint16Array(Array.prototype.slice.call(view, start, end));
};
var slice32 = function (view, start, end) {
    if (view.slice) {
        return view.slice(start, end);
    }
    return new Uint32Array(Array.prototype.slice.call(view, start, end));
};
var createTrieFromBase64 = function (base64, _byteLength) {
    var buffer = Util_1.decode(base64);
    var view32 = Array.isArray(buffer) ? Util_1.polyUint32Array(buffer) : new Uint32Array(buffer);
    var view16 = Array.isArray(buffer) ? Util_1.polyUint16Array(buffer) : new Uint16Array(buffer);
    var headerLength = 24;
    var index = slice16(view16, headerLength / 2, view32[4] / 2);
    var data = view32[5] === 2
        ? slice16(view16, (headerLength + view32[4]) / 2)
        : slice32(view32, Math.ceil((headerLength + view32[4]) / 4));
    return new Trie(view32[0], view32[1], view32[2], view32[3], index, data);
};
exports.createTrieFromBase64 = createTrieFromBase64;
var Trie = /** @class */ (function () {
    function Trie(initialValue, errorValue, highStart, highValueIndex, index, data) {
        this.initialValue = initialValue;
        this.errorValue = errorValue;
        this.highStart = highStart;
        this.highValueIndex = highValueIndex;
        this.index = index;
        this.data = data;
    }
    /**
     * Get the value for a code point as stored in the Trie.
     *
     * @param codePoint the code point
     * @return the value
     */
    Trie.prototype.get = function (codePoint) {
        var ix;
        if (codePoint >= 0) {
            if (codePoint < 0x0d800 || (codePoint > 0x0dbff && codePoint <= 0x0ffff)) {
                // Ordinary BMP code point, excluding leading surrogates.
                // BMP uses a single level lookup.  BMP index starts at offset 0 in the Trie2 index.
                // 16 bit data is stored in the index array itself.
                ix = this.index[codePoint >> exports.UTRIE2_SHIFT_2];
                ix = (ix << exports.UTRIE2_INDEX_SHIFT) + (codePoint & exports.UTRIE2_DATA_MASK);
                return this.data[ix];
            }
            if (codePoint <= 0xffff) {
                // Lead Surrogate Code Point.  A Separate index section is stored for
                // lead surrogate code units and code points.
                //   The main index has the code unit data.
                //   For this function, we need the code point data.
                // Note: this expression could be refactored for slightly improved efficiency, but
                //       surrogate code points will be so rare in practice that it's not worth it.
                ix = this.index[exports.UTRIE2_LSCP_INDEX_2_OFFSET + ((codePoint - 0xd800) >> exports.UTRIE2_SHIFT_2)];
                ix = (ix << exports.UTRIE2_INDEX_SHIFT) + (codePoint & exports.UTRIE2_DATA_MASK);
                return this.data[ix];
            }
            if (codePoint < this.highStart) {
                // Supplemental code point, use two-level lookup.
                ix = exports.UTRIE2_INDEX_1_OFFSET - exports.UTRIE2_OMITTED_BMP_INDEX_1_LENGTH + (codePoint >> exports.UTRIE2_SHIFT_1);
                ix = this.index[ix];
                ix += (codePoint >> exports.UTRIE2_SHIFT_2) & exports.UTRIE2_INDEX_2_MASK;
                ix = this.index[ix];
                ix = (ix << exports.UTRIE2_INDEX_SHIFT) + (codePoint & exports.UTRIE2_DATA_MASK);
                return this.data[ix];
            }
            if (codePoint <= 0x10ffff) {
                return this.data[this.highValueIndex];
            }
        }
        // Fall through.  The code point is outside of the legal range of 0..0x10ffff.
        return this.errorValue;
    };
    return Trie;
}());
exports.Trie = Trie;
//# sourceMappingURL=Trie.js.map