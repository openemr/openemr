"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.serializeBase64 = exports.TrieBuilder = exports.BITS_32 = exports.BITS_16 = void 0;
var Trie_1 = require("./Trie");
var base64_arraybuffer_1 = require("base64-arraybuffer");
/**
 * Trie2 constants, defining shift widths, index array lengths, etc.
 *
 * These are needed for the runtime macros but users can treat these as
 * implementation details and skip to the actual public API further below.
 */
// const UTRIE2_OPTIONS_VALUE_BITS_MASK = 0x000f;
/** Number of code points per index-1 table entry. 2048=0x800 */
var UTRIE2_CP_PER_INDEX_1_ENTRY = 1 << Trie_1.UTRIE2_SHIFT_1;
/** The alignment size of a data block. Also the granularity for compaction. */
var UTRIE2_DATA_GRANULARITY = 1 << Trie_1.UTRIE2_INDEX_SHIFT;
/* Fixed layout of the first part of the index array. ------------------- */
/**
 * The BMP part of the index-2 table is fixed and linear and starts at offset 0.
 * Length=2048=0x800=0x10000>>UTRIE2_SHIFT_2.
 */
var UTRIE2_INDEX_2_OFFSET = 0;
var UTRIE2_MAX_INDEX_1_LENGTH = 0x100000 >> Trie_1.UTRIE2_SHIFT_1;
/*
 * Fixed layout of the first part of the data array. -----------------------
 * Starts with 4 blocks (128=0x80 entries) for ASCII.
 */
/**
 * The illegal-UTF-8 data block follows the ASCII block, at offset 128=0x80.
 * Used with linear access for single bytes 0..0xbf for simple error handling.
 * Length 64=0x40, not UTRIE2_DATA_BLOCK_LENGTH.
 */
var UTRIE2_BAD_UTF8_DATA_OFFSET = 0x80;
/** The start of non-linear-ASCII data blocks, at offset 192=0xc0. */
var UTRIE2_DATA_START_OFFSET = 0xc0;
/* Building a Trie2 ---------------------------------------------------------- */
/*
 * These definitions are mostly needed by utrie2_builder.c, but also by
 * utrie2_get32() and utrie2_enum().
 */
/*
 * At build time, leave a gap in the index-2 table,
 * at least as long as the maximum lengths of the 2-byte UTF-8 index-2 table
 * and the supplementary index-1 table.
 * Round up to UTRIE2_INDEX_2_BLOCK_LENGTH for proper compacting.
 */
var UNEWTRIE2_INDEX_GAP_OFFSET = Trie_1.UTRIE2_INDEX_2_BMP_LENGTH;
var UNEWTRIE2_INDEX_GAP_LENGTH = (Trie_1.UTRIE2_UTF8_2B_INDEX_2_LENGTH + UTRIE2_MAX_INDEX_1_LENGTH + Trie_1.UTRIE2_INDEX_2_MASK) & ~Trie_1.UTRIE2_INDEX_2_MASK;
/**
 * Maximum length of the build-time index-2 array.
 * Maximum number of Unicode code points (0x110000) shifted right by UTRIE2_SHIFT_2,
 * plus the part of the index-2 table for lead surrogate code points,
 * plus the build-time index gap,
 * plus the null index-2 block.
 */
var UNEWTRIE2_MAX_INDEX_2_LENGTH = (0x110000 >> Trie_1.UTRIE2_SHIFT_2) +
    Trie_1.UTRIE2_LSCP_INDEX_2_LENGTH +
    UNEWTRIE2_INDEX_GAP_LENGTH +
    Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH;
var UNEWTRIE2_INDEX_1_LENGTH = 0x110000 >> Trie_1.UTRIE2_SHIFT_1;
/**
 * Maximum length of the build-time data array.
 * One entry per 0x110000 code points, plus the illegal-UTF-8 block and the null block,
 * plus values for the 0x400 surrogate code units.
 */
var UNEWTRIE2_MAX_DATA_LENGTH = 0x110000 + 0x40 + 0x40 + 0x400;
/* Start with allocation of 16k data entries. */
var UNEWTRIE2_INITIAL_DATA_LENGTH = 1 << 14;
/* Grow about 8x each time. */
var UNEWTRIE2_MEDIUM_DATA_LENGTH = 1 << 17;
/** The null index-2 block, following the gap in the index-2 table. */
var UNEWTRIE2_INDEX_2_NULL_OFFSET = UNEWTRIE2_INDEX_GAP_OFFSET + UNEWTRIE2_INDEX_GAP_LENGTH;
/** The start of allocated index-2 blocks. */
var UNEWTRIE2_INDEX_2_START_OFFSET = UNEWTRIE2_INDEX_2_NULL_OFFSET + Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH;
/**
 * The null data block.
 * Length 64=0x40 even if UTRIE2_DATA_BLOCK_LENGTH is smaller,
 * to work with 6-bit trail bytes from 2-byte UTF-8.
 */
var UNEWTRIE2_DATA_NULL_OFFSET = UTRIE2_DATA_START_OFFSET;
/** The start of allocated data blocks. */
var UNEWTRIE2_DATA_START_OFFSET = UNEWTRIE2_DATA_NULL_OFFSET + 0x40;
/**
 * The start of data blocks for U+0800 and above.
 * Below, compaction uses a block length of 64 for 2-byte UTF-8.
 * From here on, compaction uses UTRIE2_DATA_BLOCK_LENGTH.
 * Data values for 0x780 code points beyond ASCII.
 */
var UNEWTRIE2_DATA_0800_OFFSET = UNEWTRIE2_DATA_START_OFFSET + 0x780;
/**
 * Maximum length of the runtime index array.
 * Limited by its own 16-bit index values, and by uint16_t UTrie2Header.indexLength.
 * (The actual maximum length is lower,
 * (0x110000>>UTRIE2_SHIFT_2)+UTRIE2_UTF8_2B_INDEX_2_LENGTH+UTRIE2_MAX_INDEX_1_LENGTH.)
 */
var UTRIE2_MAX_INDEX_LENGTH = 0xffff;
/**
 * Maximum length of the runtime data array.
 * Limited by 16-bit index values that are left-shifted by UTRIE2_INDEX_SHIFT,
 * and by uint16_t UTrie2Header.shiftedDataLength.
 */
var UTRIE2_MAX_DATA_LENGTH = 0xffff << Trie_1.UTRIE2_INDEX_SHIFT;
exports.BITS_16 = 16;
exports.BITS_32 = 32;
var isHighSurrogate = function (c) { return c >= 0xd800 && c <= 0xdbff; };
var equalInt = function (a, s, t, length) {
    for (var i = 0; i < length; i++) {
        if (a[s + i] !== a[t + i]) {
            return false;
        }
    }
    return true;
};
var TrieBuilder = /** @class */ (function () {
    function TrieBuilder(initialValue, errorValue) {
        if (initialValue === void 0) { initialValue = 0; }
        if (errorValue === void 0) { errorValue = 0; }
        this.initialValue = initialValue;
        this.errorValue = errorValue;
        this.highStart = 0x110000;
        this.data = new Uint32Array(UNEWTRIE2_INITIAL_DATA_LENGTH);
        this.dataCapacity = UNEWTRIE2_INITIAL_DATA_LENGTH;
        this.highStart = 0x110000;
        this.firstFreeBlock = 0; /* no free block in the list */
        this.isCompacted = false;
        this.index1 = new Uint32Array(UNEWTRIE2_INDEX_1_LENGTH);
        this.index2 = new Uint32Array(UNEWTRIE2_MAX_INDEX_2_LENGTH);
        /*
         * Multi-purpose per-data-block table.
         *
         * Before compacting:
         *
         * Per-data-block reference counters/free-block list.
         *  0: unused
         * >0: reference counter (number of index-2 entries pointing here)
         * <0: next free data block in free-block list
         *
         * While compacting:
         *
         * Map of adjusted indexes, used in compactData() and compactIndex2().
         * Maps from original indexes to new ones.
         */
        this.map = new Uint32Array(UNEWTRIE2_MAX_DATA_LENGTH >> Trie_1.UTRIE2_SHIFT_2);
        /*
         * preallocate and reset
         * - ASCII
         * - the bad-UTF-8-data block
         * - the null data block
         */
        var i, j;
        for (i = 0; i < 0x80; ++i) {
            this.data[i] = initialValue;
        }
        for (; i < 0xc0; ++i) {
            this.data[i] = errorValue;
        }
        for (i = UNEWTRIE2_DATA_NULL_OFFSET; i < UNEWTRIE2_DATA_START_OFFSET; ++i) {
            this.data[i] = initialValue;
        }
        this.dataNullOffset = UNEWTRIE2_DATA_NULL_OFFSET;
        this.dataLength = UNEWTRIE2_DATA_START_OFFSET;
        /* set the index-2 indexes for the 2=0x80>>UTRIE2_SHIFT_2 ASCII data blocks */
        for (i = 0, j = 0; j < 0x80; ++i, j += Trie_1.UTRIE2_DATA_BLOCK_LENGTH) {
            this.index2[i] = j;
            this.map[i] = 1;
        }
        /* reference counts for the bad-UTF-8-data block */
        for (; j < 0xc0; ++i, j += Trie_1.UTRIE2_DATA_BLOCK_LENGTH) {
            this.map[i] = 0;
        }
        /*
         * Reference counts for the null data block: all blocks except for the ASCII blocks.
         * Plus 1 so that we don't drop this block during compaction.
         * Plus as many as needed for lead surrogate code points.
         */
        /* i==newTrie->dataNullOffset */
        this.map[i++] = (0x110000 >> Trie_1.UTRIE2_SHIFT_2) - (0x80 >> Trie_1.UTRIE2_SHIFT_2) + 1 + Trie_1.UTRIE2_LSCP_INDEX_2_LENGTH;
        j += Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
        for (; j < UNEWTRIE2_DATA_START_OFFSET; ++i, j += Trie_1.UTRIE2_DATA_BLOCK_LENGTH) {
            this.map[i] = 0;
        }
        /*
         * set the remaining indexes in the BMP index-2 block
         * to the null data block
         */
        for (i = 0x80 >> Trie_1.UTRIE2_SHIFT_2; i < Trie_1.UTRIE2_INDEX_2_BMP_LENGTH; ++i) {
            this.index2[i] = UNEWTRIE2_DATA_NULL_OFFSET;
        }
        /*
         * Fill the index gap with impossible values so that compaction
         * does not overlap other index-2 blocks with the gap.
         */
        for (i = 0; i < UNEWTRIE2_INDEX_GAP_LENGTH; ++i) {
            this.index2[UNEWTRIE2_INDEX_GAP_OFFSET + i] = -1;
        }
        /* set the indexes in the null index-2 block */
        for (i = 0; i < Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH; ++i) {
            this.index2[UNEWTRIE2_INDEX_2_NULL_OFFSET + i] = UNEWTRIE2_DATA_NULL_OFFSET;
        }
        this.index2NullOffset = UNEWTRIE2_INDEX_2_NULL_OFFSET;
        this.index2Length = UNEWTRIE2_INDEX_2_START_OFFSET;
        /* set the index-1 indexes for the linear index-2 block */
        for (i = 0, j = 0; i < Trie_1.UTRIE2_OMITTED_BMP_INDEX_1_LENGTH; ++i, j += Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH) {
            this.index1[i] = j;
        }
        /* set the remaining index-1 indexes to the null index-2 block */
        for (; i < UNEWTRIE2_INDEX_1_LENGTH; ++i) {
            this.index1[i] = UNEWTRIE2_INDEX_2_NULL_OFFSET;
        }
        /*
         * Preallocate and reset data for U+0080..U+07ff,
         * for 2-byte UTF-8 which will be compacted in 64-blocks
         * even if UTRIE2_DATA_BLOCK_LENGTH is smaller.
         */
        for (i = 0x80; i < 0x800; i += Trie_1.UTRIE2_DATA_BLOCK_LENGTH) {
            this.set(i, initialValue);
        }
    }
    /**
     * Set a value for a code point.
     *
     * @param c the code point
     * @param value the value
     */
    TrieBuilder.prototype.set = function (c, value) {
        if (c < 0 || c > 0x10ffff) {
            throw new Error('Invalid code point.');
        }
        this._set(c, true, value);
        return this;
    };
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
    TrieBuilder.prototype.setRange = function (start, end, value, overwrite) {
        if (overwrite === void 0) { overwrite = false; }
        /*
         * repeat value in [start..end]
         * mark index values for repeat-data blocks by setting bit 31 of the index values
         * fill around existing values if any, if(overwrite)
         */
        var block, rest, repeatBlock;
        if (start > 0x10ffff || start < 0 || end > 0x10ffff || end < 0 || start > end) {
            throw new Error('Invalid code point range.');
        }
        if (!overwrite && value === this.initialValue) {
            return this; /* nothing to do */
        }
        if (this.isCompacted) {
            throw new Error('Trie was already compacted');
        }
        var limit = end + 1;
        if ((start & Trie_1.UTRIE2_DATA_MASK) !== 0) {
            /* set partial block at [start..following block boundary[ */
            block = this.getDataBlock(start, true);
            var nextStart = (start + Trie_1.UTRIE2_DATA_BLOCK_LENGTH) & ~Trie_1.UTRIE2_DATA_MASK;
            if (nextStart <= limit) {
                this.fillBlock(block, start & Trie_1.UTRIE2_DATA_MASK, Trie_1.UTRIE2_DATA_BLOCK_LENGTH, value, this.initialValue, overwrite);
                start = nextStart;
            }
            else {
                this.fillBlock(block, start & Trie_1.UTRIE2_DATA_MASK, limit & Trie_1.UTRIE2_DATA_MASK, value, this.initialValue, overwrite);
                return this;
            }
        }
        /* number of positions in the last, partial block */
        rest = limit & Trie_1.UTRIE2_DATA_MASK;
        /* round down limit to a block boundary */
        limit &= ~Trie_1.UTRIE2_DATA_MASK;
        /* iterate over all-value blocks */
        repeatBlock = value === this.initialValue ? this.dataNullOffset : -1;
        while (start < limit) {
            var i2 = void 0;
            var setRepeatBlock = false;
            if (value === this.initialValue && this.isInNullBlock(start, true)) {
                start += Trie_1.UTRIE2_DATA_BLOCK_LENGTH; /* nothing to do */
                continue;
            }
            /* get index value */
            i2 = this.getIndex2Block(start, true);
            i2 += (start >> Trie_1.UTRIE2_SHIFT_2) & Trie_1.UTRIE2_INDEX_2_MASK;
            block = this.index2[i2];
            if (this.isWritableBlock(block)) {
                /* already allocated */
                if (overwrite && block >= UNEWTRIE2_DATA_0800_OFFSET) {
                    /*
                     * We overwrite all values, and it's not a
                     * protected (ASCII-linear or 2-byte UTF-8) block:
                     * replace with the repeatBlock.
                     */
                    setRepeatBlock = true;
                }
                else {
                    /* !overwrite, or protected block: just write the values into this block */
                    this.fillBlock(block, 0, Trie_1.UTRIE2_DATA_BLOCK_LENGTH, value, this.initialValue, overwrite);
                }
            }
            else if (this.data[block] !== value && (overwrite || block === this.dataNullOffset)) {
                /*
                 * Set the repeatBlock instead of the null block or previous repeat block:
                 *
                 * If !isWritableBlock() then all entries in the block have the same value
                 * because it's the null block or a range block (the repeatBlock from a previous
                 * call to utrie2_setRange32()).
                 * No other blocks are used multiple times before compacting.
                 *
                 * The null block is the only non-writable block with the initialValue because
                 * of the repeatBlock initialization above. (If value==initialValue, then
                 * the repeatBlock will be the null data block.)
                 *
                 * We set our repeatBlock if the desired value differs from the block's value,
                 * and if we overwrite any data or if the data is all initial values
                 * (which is the same as the block being the null block, see above).
                 */
                setRepeatBlock = true;
            }
            if (setRepeatBlock) {
                if (repeatBlock >= 0) {
                    this.setIndex2Entry(i2, repeatBlock);
                }
                else {
                    /* create and set and fill the repeatBlock */
                    repeatBlock = this.getDataBlock(start, true);
                    this.writeBlock(repeatBlock, value);
                }
            }
            start += Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
        }
        if (rest > 0) {
            /* set partial block at [last block boundary..limit[ */
            block = this.getDataBlock(start, true);
            this.fillBlock(block, 0, rest, value, this.initialValue, overwrite);
        }
        return this;
    };
    /**
     * Get the value for a code point as stored in the Trie2.
     *
     * @param codePoint the code point
     * @return the value
     */
    TrieBuilder.prototype.get = function (codePoint) {
        if (codePoint < 0 || codePoint > 0x10ffff) {
            return this.errorValue;
        }
        else {
            return this._get(codePoint, true);
        }
    };
    TrieBuilder.prototype._get = function (c, fromLSCP) {
        var i2;
        if (c >= this.highStart && (!(c >= 0xd800 && c < 0xdc00) || fromLSCP)) {
            return this.data[this.dataLength - UTRIE2_DATA_GRANULARITY];
        }
        if (c >= 0xd800 && c < 0xdc00 && fromLSCP) {
            i2 = Trie_1.UTRIE2_LSCP_INDEX_2_OFFSET - (0xd800 >> Trie_1.UTRIE2_SHIFT_2) + (c >> Trie_1.UTRIE2_SHIFT_2);
        }
        else {
            i2 = this.index1[c >> Trie_1.UTRIE2_SHIFT_1] + ((c >> Trie_1.UTRIE2_SHIFT_2) & Trie_1.UTRIE2_INDEX_2_MASK);
        }
        var block = this.index2[i2];
        return this.data[block + (c & Trie_1.UTRIE2_DATA_MASK)];
    };
    TrieBuilder.prototype.freeze = function (valueBits) {
        if (valueBits === void 0) { valueBits = exports.BITS_32; }
        var i;
        var allIndexesLength;
        var dataMove; /* >0 if the data is moved to the end of the index array */
        /* compact if necessary */
        if (!this.isCompacted) {
            this.compactTrie();
        }
        allIndexesLength = this.highStart <= 0x10000 ? Trie_1.UTRIE2_INDEX_1_OFFSET : this.index2Length;
        if (valueBits === exports.BITS_16) {
            // dataMove = allIndexesLength;
            dataMove = 0;
        }
        else {
            dataMove = 0;
        }
        /* are indexLength and dataLength within limits? */
        if (
        /* for unshifted indexLength */
        allIndexesLength > UTRIE2_MAX_INDEX_LENGTH ||
            /* for unshifted dataNullOffset */
            dataMove + this.dataNullOffset > 0xffff ||
            /* for unshifted 2-byte UTF-8 index-2 values */
            dataMove + UNEWTRIE2_DATA_0800_OFFSET > 0xffff ||
            /* for shiftedDataLength */
            dataMove + this.dataLength > UTRIE2_MAX_DATA_LENGTH) {
            throw new Error('Trie data is too large.');
        }
        var index = new Uint16Array(allIndexesLength);
        /* write the index-2 array values shifted right by UTRIE2_INDEX_SHIFT, after adding dataMove */
        var destIdx = 0;
        for (i = 0; i < Trie_1.UTRIE2_INDEX_2_BMP_LENGTH; i++) {
            index[destIdx++] = (this.index2[i] + dataMove) >> Trie_1.UTRIE2_INDEX_SHIFT;
        }
        /* write UTF-8 2-byte index-2 values, not right-shifted */
        for (i = 0; i < 0xc2 - 0xc0; ++i) {
            /* C0..C1 */
            index[destIdx++] = dataMove + UTRIE2_BAD_UTF8_DATA_OFFSET;
        }
        for (; i < 0xe0 - 0xc0; ++i) {
            /* C2..DF */
            index[destIdx++] = dataMove + this.index2[i << (6 - Trie_1.UTRIE2_SHIFT_2)];
        }
        if (this.highStart > 0x10000) {
            var index1Length = (this.highStart - 0x10000) >> Trie_1.UTRIE2_SHIFT_1;
            var index2Offset = Trie_1.UTRIE2_INDEX_2_BMP_LENGTH + Trie_1.UTRIE2_UTF8_2B_INDEX_2_LENGTH + index1Length;
            /* write 16-bit index-1 values for supplementary code points */
            for (i = 0; i < index1Length; i++) {
                index[destIdx++] = UTRIE2_INDEX_2_OFFSET + this.index1[i + Trie_1.UTRIE2_OMITTED_BMP_INDEX_1_LENGTH];
            }
            /*
             * write the index-2 array values for supplementary code points,
             * shifted right by UTRIE2_INDEX_SHIFT, after adding dataMove
             */
            for (i = 0; i < this.index2Length - index2Offset; i++) {
                index[destIdx++] = (dataMove + this.index2[index2Offset + i]) >> Trie_1.UTRIE2_INDEX_SHIFT;
            }
        }
        /* write the 16/32-bit data array */
        switch (valueBits) {
            case exports.BITS_16:
                /* write 16-bit data values */
                var data16 = new Uint16Array(this.dataLength);
                for (i = 0; i < this.dataLength; i++) {
                    data16[i] = this.data[i];
                }
                return new Trie_1.Trie(this.initialValue, this.errorValue, this.highStart, dataMove + this.dataLength - UTRIE2_DATA_GRANULARITY, index, data16);
            case exports.BITS_32:
                /* write 32-bit data values */
                var data32 = new Uint32Array(this.dataLength);
                for (i = 0; i < this.dataLength; i++) {
                    data32[i] = this.data[i];
                }
                return new Trie_1.Trie(this.initialValue, this.errorValue, this.highStart, dataMove + this.dataLength - UTRIE2_DATA_GRANULARITY, index, data32);
            default:
                throw new Error('Bits should be either 16 or 32');
        }
    };
    /*
     * Find the start of the last range in the trie by enumerating backward.
     * Indexes for supplementary code points higher than this will be omitted.
     */
    TrieBuilder.prototype.findHighStart = function (highValue) {
        var value;
        var i2, j, i2Block, prevI2Block, block, prevBlock;
        /* set variables for previous range */
        if (highValue === this.initialValue) {
            prevI2Block = this.index2NullOffset;
            prevBlock = this.dataNullOffset;
        }
        else {
            prevI2Block = -1;
            prevBlock = -1;
        }
        var prev = 0x110000;
        /* enumerate index-2 blocks */
        var i1 = UNEWTRIE2_INDEX_1_LENGTH;
        var c = prev;
        while (c > 0) {
            i2Block = this.index1[--i1];
            if (i2Block === prevI2Block) {
                /* the index-2 block is the same as the previous one, and filled with highValue */
                c -= UTRIE2_CP_PER_INDEX_1_ENTRY;
                continue;
            }
            prevI2Block = i2Block;
            if (i2Block === this.index2NullOffset) {
                /* this is the null index-2 block */
                if (highValue !== this.initialValue) {
                    return c;
                }
                c -= UTRIE2_CP_PER_INDEX_1_ENTRY;
            }
            else {
                /* enumerate data blocks for one index-2 block */
                for (i2 = Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH; i2 > 0;) {
                    block = this.index2[i2Block + --i2];
                    if (block === prevBlock) {
                        /* the block is the same as the previous one, and filled with highValue */
                        c -= Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
                        continue;
                    }
                    prevBlock = block;
                    if (block === this.dataNullOffset) {
                        /* this is the null data block */
                        if (highValue !== this.initialValue) {
                            return c;
                        }
                        c -= Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
                    }
                    else {
                        for (j = Trie_1.UTRIE2_DATA_BLOCK_LENGTH; j > 0;) {
                            value = this.data[block + --j];
                            if (value !== highValue) {
                                return c;
                            }
                            --c;
                        }
                    }
                }
            }
        }
        /* deliver last range */
        return 0;
    };
    /*
     * Compact a build-time trie.
     *
     * The compaction
     * - removes blocks that are identical with earlier ones
     * - overlaps adjacent blocks as much as possible (if overlap==TRUE)
     * - moves blocks in steps of the data granularity
     * - moves and overlaps blocks that overlap with multiple values in the overlap region
     *
     * It does not
     * - try to move and overlap blocks that are not already adjacent
     */
    TrieBuilder.prototype.compactData = function () {
        var start, movedStart;
        var blockLength, overlap;
        var i, mapIndex, blockCount;
        /* do not compact linear-ASCII data */
        var newStart = UTRIE2_DATA_START_OFFSET;
        for (start = 0, i = 0; start < newStart; start += Trie_1.UTRIE2_DATA_BLOCK_LENGTH, ++i) {
            this.map[i] = start;
        }
        /*
         * Start with a block length of 64 for 2-byte UTF-8,
         * then switch to UTRIE2_DATA_BLOCK_LENGTH.
         */
        blockLength = 64;
        blockCount = blockLength >> Trie_1.UTRIE2_SHIFT_2;
        for (start = newStart; start < this.dataLength;) {
            /*
             * start: index of first entry of current block
             * newStart: index where the current block is to be moved
             *           (right after current end of already-compacted data)
             */
            if (start === UNEWTRIE2_DATA_0800_OFFSET) {
                blockLength = Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
                blockCount = 1;
            }
            /* skip blocks that are not used */
            if (this.map[start >> Trie_1.UTRIE2_SHIFT_2] <= 0) {
                /* advance start to the next block */
                start += blockLength;
                /* leave newStart with the previous block! */
                continue;
            }
            /* search for an identical block */
            movedStart = this.findSameDataBlock(newStart, start, blockLength);
            if (movedStart >= 0) {
                /* found an identical block, set the other block's index value for the current block */
                for (i = blockCount, mapIndex = start >> Trie_1.UTRIE2_SHIFT_2; i > 0; --i) {
                    this.map[mapIndex++] = movedStart;
                    movedStart += Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
                }
                /* advance start to the next block */
                start += blockLength;
                /* leave newStart with the previous block! */
                continue;
            }
            /* see if the beginning of this block can be overlapped with the end of the previous block */
            /* look for maximum overlap (modulo granularity) with the previous, adjacent block */
            for (overlap = blockLength - UTRIE2_DATA_GRANULARITY; overlap > 0 && !equalInt(this.data, newStart - overlap, start, overlap); overlap -= UTRIE2_DATA_GRANULARITY) { }
            if (overlap > 0 || newStart < start) {
                /* some overlap, or just move the whole block */
                movedStart = newStart - overlap;
                for (i = blockCount, mapIndex = start >> Trie_1.UTRIE2_SHIFT_2; i > 0; --i) {
                    this.map[mapIndex++] = movedStart;
                    movedStart += Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
                }
                /* move the non-overlapping indexes to their new positions */
                start += overlap;
                for (i = blockLength - overlap; i > 0; --i) {
                    this.data[newStart++] = this.data[start++];
                }
            }
            else {
                /* no overlap && newStart==start */
                for (i = blockCount, mapIndex = start >> Trie_1.UTRIE2_SHIFT_2; i > 0; --i) {
                    this.map[mapIndex++] = start;
                    start += Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
                }
                newStart = start;
            }
        }
        /* now adjust the index-2 table */
        for (i = 0; i < this.index2Length; ++i) {
            if (i === UNEWTRIE2_INDEX_GAP_OFFSET) {
                /* Gap indexes are invalid (-1). Skip over the gap. */
                i += UNEWTRIE2_INDEX_GAP_LENGTH;
            }
            this.index2[i] = this.map[this.index2[i] >> Trie_1.UTRIE2_SHIFT_2];
        }
        this.dataNullOffset = this.map[this.dataNullOffset >> Trie_1.UTRIE2_SHIFT_2];
        /* ensure dataLength alignment */
        while ((newStart & (UTRIE2_DATA_GRANULARITY - 1)) !== 0) {
            this.data[newStart++] = this.initialValue;
        }
        this.dataLength = newStart;
    };
    TrieBuilder.prototype.findSameDataBlock = function (dataLength, otherBlock, blockLength) {
        var block = 0;
        /* ensure that we do not even partially get past dataLength */
        dataLength -= blockLength;
        for (; block <= dataLength; block += UTRIE2_DATA_GRANULARITY) {
            if (equalInt(this.data, block, otherBlock, blockLength)) {
                return block;
            }
        }
        return -1;
    };
    TrieBuilder.prototype.compactTrie = function () {
        var highValue = this.get(0x10ffff);
        /* find highStart and round it up */
        var localHighStart = this.findHighStart(highValue);
        localHighStart = (localHighStart + (UTRIE2_CP_PER_INDEX_1_ENTRY - 1)) & ~(UTRIE2_CP_PER_INDEX_1_ENTRY - 1);
        if (localHighStart === 0x110000) {
            highValue = this.errorValue;
        }
        /*
         * Set trie->highStart only after utrie2_get32(trie, highStart).
         * Otherwise utrie2_get32(trie, highStart) would try to read the highValue.
         */
        this.highStart = localHighStart;
        if (this.highStart < 0x110000) {
            /* Blank out [highStart..10ffff] to release associated data blocks. */
            var suppHighStart = this.highStart <= 0x10000 ? 0x10000 : this.highStart;
            this.setRange(suppHighStart, 0x10ffff, this.initialValue, true);
        }
        this.compactData();
        if (this.highStart > 0x10000) {
            this.compactIndex2();
        }
        /*
         * Store the highValue in the data array and round up the dataLength.
         * Must be done after compactData() because that assumes that dataLength
         * is a multiple of UTRIE2_DATA_BLOCK_LENGTH.
         */
        this.data[this.dataLength++] = highValue;
        while ((this.dataLength & (UTRIE2_DATA_GRANULARITY - 1)) !== 0) {
            this.data[this.dataLength++] = this.initialValue;
        }
        this.isCompacted = true;
    };
    TrieBuilder.prototype.compactIndex2 = function () {
        var i, start, movedStart, overlap;
        /* do not compact linear-BMP index-2 blocks */
        var newStart = Trie_1.UTRIE2_INDEX_2_BMP_LENGTH;
        for (start = 0, i = 0; start < newStart; start += Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH, ++i) {
            this.map[i] = start;
        }
        /* Reduce the index table gap to what will be needed at runtime. */
        newStart += Trie_1.UTRIE2_UTF8_2B_INDEX_2_LENGTH + ((this.highStart - 0x10000) >> Trie_1.UTRIE2_SHIFT_1);
        for (start = UNEWTRIE2_INDEX_2_NULL_OFFSET; start < this.index2Length;) {
            /*
             * start: index of first entry of current block
             * newStart: index where the current block is to be moved
             *           (right after current end of already-compacted data)
             */
            /* search for an identical block */
            if ((movedStart = this.findSameIndex2Block(newStart, start)) >= 0) {
                /* found an identical block, set the other block's index value for the current block */
                this.map[start >> Trie_1.UTRIE2_SHIFT_1_2] = movedStart;
                /* advance start to the next block */
                start += Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH;
                /* leave newStart with the previous block! */
                continue;
            }
            /* see if the beginning of this block can be overlapped with the end of the previous block */
            /* look for maximum overlap with the previous, adjacent block */
            for (overlap = Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH - 1; overlap > 0 && !equalInt(this.index2, newStart - overlap, start, overlap); --overlap) { }
            if (overlap > 0 || newStart < start) {
                /* some overlap, or just move the whole block */
                this.map[start >> Trie_1.UTRIE2_SHIFT_1_2] = newStart - overlap;
                /* move the non-overlapping indexes to their new positions */
                start += overlap;
                for (i = Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH - overlap; i > 0; --i) {
                    this.index2[newStart++] = this.index2[start++];
                }
            }
            else {
                /* no overlap && newStart==start */ this.map[start >> Trie_1.UTRIE2_SHIFT_1_2] = start;
                start += Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH;
                newStart = start;
            }
        }
        /* now adjust the index-1 table */
        for (i = 0; i < UNEWTRIE2_INDEX_1_LENGTH; ++i) {
            this.index1[i] = this.map[this.index1[i] >> Trie_1.UTRIE2_SHIFT_1_2];
        }
        this.index2NullOffset = this.map[this.index2NullOffset >> Trie_1.UTRIE2_SHIFT_1_2];
        /*
         * Ensure data table alignment:
         * Needs to be granularity-aligned for 16-bit trie
         * (so that dataMove will be down-shiftable),
         * and 2-aligned for uint32_t data.
         */
        while ((newStart & ((UTRIE2_DATA_GRANULARITY - 1) | 1)) !== 0) {
            /* Arbitrary value: 0x3fffc not possible for real data. */
            this.index2[newStart++] = 0x0000ffff << Trie_1.UTRIE2_INDEX_SHIFT;
        }
        this.index2Length = newStart;
    };
    TrieBuilder.prototype.findSameIndex2Block = function (index2Length, otherBlock) {
        /* ensure that we do not even partially get past index2Length */
        index2Length -= Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH;
        for (var block = 0; block <= index2Length; ++block) {
            if (equalInt(this.index2, block, otherBlock, Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH)) {
                return block;
            }
        }
        return -1;
    };
    TrieBuilder.prototype._set = function (c, forLSCP, value) {
        if (this.isCompacted) {
            throw new Error('Trie was already compacted');
        }
        var block = this.getDataBlock(c, forLSCP);
        this.data[block + (c & Trie_1.UTRIE2_DATA_MASK)] = value;
        return this;
    };
    TrieBuilder.prototype.writeBlock = function (block, value) {
        var limit = block + Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
        while (block < limit) {
            this.data[block++] = value;
        }
    };
    TrieBuilder.prototype.isInNullBlock = function (c, forLSCP) {
        var i2 = isHighSurrogate(c) && forLSCP
            ? Trie_1.UTRIE2_LSCP_INDEX_2_OFFSET - (0xd800 >> Trie_1.UTRIE2_SHIFT_2) + (c >> Trie_1.UTRIE2_SHIFT_2)
            : this.index1[c >> Trie_1.UTRIE2_SHIFT_1] + ((c >> Trie_1.UTRIE2_SHIFT_2) & Trie_1.UTRIE2_INDEX_2_MASK);
        var block = this.index2[i2];
        return block === this.dataNullOffset;
    };
    TrieBuilder.prototype.fillBlock = function (block, start, limit, value, initialValue, overwrite) {
        var pLimit = block + limit;
        if (overwrite) {
            for (var i = block + start; i < pLimit; i++) {
                this.data[i] = value;
            }
        }
        else {
            for (var i = block + start; i < pLimit; i++) {
                if (this.data[i] === initialValue) {
                    this.data[i] = value;
                }
            }
        }
    };
    TrieBuilder.prototype.setIndex2Entry = function (i2, block) {
        ++this.map[block >> Trie_1.UTRIE2_SHIFT_2]; /* increment first, in case block==oldBlock! */
        var oldBlock = this.index2[i2];
        if (0 === --this.map[oldBlock >> Trie_1.UTRIE2_SHIFT_2]) {
            this.releaseDataBlock(oldBlock);
        }
        this.index2[i2] = block;
    };
    TrieBuilder.prototype.releaseDataBlock = function (block) {
        /* put this block at the front of the free-block chain */
        this.map[block >> Trie_1.UTRIE2_SHIFT_2] = -this.firstFreeBlock;
        this.firstFreeBlock = block;
    };
    TrieBuilder.prototype.getDataBlock = function (c, forLSCP) {
        var i2 = this.getIndex2Block(c, forLSCP);
        i2 += (c >> Trie_1.UTRIE2_SHIFT_2) & Trie_1.UTRIE2_INDEX_2_MASK;
        var oldBlock = this.index2[i2];
        if (this.isWritableBlock(oldBlock)) {
            return oldBlock;
        }
        /* allocate a new data block */
        var newBlock = this.allocDataBlock(oldBlock);
        this.setIndex2Entry(i2, newBlock);
        return newBlock;
    };
    TrieBuilder.prototype.isWritableBlock = function (block) {
        return block !== this.dataNullOffset && 1 === this.map[block >> Trie_1.UTRIE2_SHIFT_2];
    };
    TrieBuilder.prototype.getIndex2Block = function (c, forLSCP) {
        if (c >= 0xd800 && c < 0xdc00 && forLSCP) {
            return Trie_1.UTRIE2_LSCP_INDEX_2_OFFSET;
        }
        var i1 = c >> Trie_1.UTRIE2_SHIFT_1;
        var i2 = this.index1[i1];
        if (i2 === this.index2NullOffset) {
            i2 = this.allocIndex2Block();
            this.index1[i1] = i2;
        }
        return i2;
    };
    TrieBuilder.prototype.allocDataBlock = function (copyBlock) {
        var newBlock;
        if (this.firstFreeBlock !== 0) {
            /* get the first free block */
            newBlock = this.firstFreeBlock;
            this.firstFreeBlock = -this.map[newBlock >> Trie_1.UTRIE2_SHIFT_2];
        }
        else {
            /* get a new block from the high end */
            newBlock = this.dataLength;
            var newTop = newBlock + Trie_1.UTRIE2_DATA_BLOCK_LENGTH;
            if (newTop > this.dataCapacity) {
                var capacity = void 0;
                /* out of memory in the data array */
                if (this.dataCapacity < UNEWTRIE2_MEDIUM_DATA_LENGTH) {
                    capacity = UNEWTRIE2_MEDIUM_DATA_LENGTH;
                }
                else if (this.dataCapacity < UNEWTRIE2_MAX_DATA_LENGTH) {
                    capacity = UNEWTRIE2_MAX_DATA_LENGTH;
                }
                else {
                    /*
                     * Should never occur.
                     * Either UNEWTRIE2_MAX_DATA_LENGTH is incorrect,
                     * or the code writes more values than should be possible.
                     */
                    throw new Error('Internal error in Trie creation.');
                }
                var newData = new Uint32Array(capacity);
                newData.set(this.data.subarray(0, this.dataLength));
                this.data = newData;
                this.dataCapacity = capacity;
            }
            this.dataLength = newTop;
        }
        this.data.set(this.data.subarray(copyBlock, copyBlock + Trie_1.UTRIE2_DATA_BLOCK_LENGTH), newBlock);
        this.map[newBlock >> Trie_1.UTRIE2_SHIFT_2] = 0;
        return newBlock;
    };
    TrieBuilder.prototype.allocIndex2Block = function () {
        var newBlock = this.index2Length;
        var newTop = newBlock + Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH;
        if (newTop > this.index2.length) {
            throw new Error('Internal error in Trie creation.');
            /*
             * Should never occur.
             * Either UTRIE2_MAX_BUILD_TIME_INDEX_LENGTH is incorrect,
             * or the code writes more values than should be possible.
             */
        }
        this.index2Length = newTop;
        this.index2.set(this.index2.subarray(this.index2NullOffset, this.index2NullOffset + Trie_1.UTRIE2_INDEX_2_BLOCK_LENGTH), newBlock);
        return newBlock;
    };
    return TrieBuilder;
}());
exports.TrieBuilder = TrieBuilder;
var serializeBase64 = function (trie) {
    var index = trie.index;
    var data = trie.data;
    if (!(index instanceof Uint16Array) || !(data instanceof Uint16Array || data instanceof Uint32Array)) {
        throw new Error('TrieBuilder serializer only support TypedArrays');
    }
    var headerLength = Uint32Array.BYTES_PER_ELEMENT * 6;
    var bufferLength = headerLength + index.byteLength + data.byteLength;
    var buffer = new ArrayBuffer(Math.ceil(bufferLength / 4) * 4);
    var view32 = new Uint32Array(buffer);
    var view16 = new Uint16Array(buffer);
    view32[0] = trie.initialValue;
    view32[1] = trie.errorValue;
    view32[2] = trie.highStart;
    view32[3] = trie.highValueIndex;
    view32[4] = index.byteLength;
    // $FlowFixMe
    view32[5] = data.BYTES_PER_ELEMENT;
    view16.set(index, headerLength / Uint16Array.BYTES_PER_ELEMENT);
    if (data.BYTES_PER_ELEMENT === Uint16Array.BYTES_PER_ELEMENT) {
        view16.set(data, (headerLength + index.byteLength) / Uint16Array.BYTES_PER_ELEMENT);
    }
    else {
        view32.set(data, Math.ceil((headerLength + index.byteLength) / Uint32Array.BYTES_PER_ELEMENT));
    }
    return base64_arraybuffer_1.encode(buffer);
};
exports.serializeBase64 = serializeBase64;
//# sourceMappingURL=TrieBuilder.js.map