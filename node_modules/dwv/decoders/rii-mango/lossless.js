(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.jpeg = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};


/*** Constructor ***/
jpeg.lossless.ComponentSpec = jpeg.lossless.ComponentSpec || function () {
    this.hSamp = 0; // Horizontal sampling factor
    this.quantTableSel = 0; // Quantization table destination selector
    this.vSamp = 0; // Vertical
};


/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.ComponentSpec;
}

},{}],2:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};


/*** Constructor ***/
jpeg.lossless.DataStream = jpeg.lossless.DataStream || function (data, offset, length) {
    // Note: DataView is much slower than Int8Array
    // this.buffer = new DataView(data, offset, length);
    this.buffer = new Uint8Array(data, offset, length);
    this.index = 0;
};



jpeg.lossless.DataStream.prototype.get16 = function () {
    // var value = this.buffer.getUint16(this.index, false);
    var value = (this.buffer[this.index] << 8) + this.buffer[this.index + 1]; // DataView is big-endian by default
    this.index += 2;
    return value;
};



jpeg.lossless.DataStream.prototype.get8 = function () {
    // var value = this.buffer.getUint8(this.index);
    var value = this.buffer[this.index];
    this.index += 1;
    return value;
};


/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.DataStream;
}

},{}],3:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};
jpeg.lossless.DataStream = jpeg.lossless.DataStream || ((typeof require !== 'undefined') ? require('./data-stream.js') : null);
jpeg.lossless.HuffmanTable = jpeg.lossless.HuffmanTable || ((typeof require !== 'undefined') ? require('./huffman-table.js') : null);
jpeg.lossless.QuantizationTable = jpeg.lossless.QuantizationTable || ((typeof require !== 'undefined') ? require('./quantization-table.js') : null);
jpeg.lossless.ScanHeader = jpeg.lossless.ScanHeader || ((typeof require !== 'undefined') ? require('./scan-header.js') : null);
jpeg.lossless.FrameHeader = jpeg.lossless.FrameHeader || ((typeof require !== 'undefined') ? require('./frame-header.js') : null);
jpeg.lossless.Utils = jpeg.lossless.Utils || ((typeof require !== 'undefined') ? require('./utils.js') : null);


/*** Constructor ***/

/**
 * The Decoder constructor.
 * @property {number} xDim - size of x dimension
 * @property {number} yDim - size of y dimension
 * @property {number} numComp - number of components
 * @property {number} numBytes - number of bytes per component
 * @type {Function}
 */
jpeg.lossless.Decoder = jpeg.lossless.Decoder || function (buffer, numBytes) {
    this.buffer = buffer;
    this.frame = new jpeg.lossless.FrameHeader();
    this.huffTable = new jpeg.lossless.HuffmanTable();
    this.quantTable = new jpeg.lossless.QuantizationTable();
    this.scan = new jpeg.lossless.ScanHeader();
    this.DU = jpeg.lossless.Utils.createArray(10, 4, 64); // at most 10 data units in a MCU, at most 4 data units in one component
    this.HuffTab = jpeg.lossless.Utils.createArray(4, 2, 50 * 256);
    this.IDCT_Source = [];
    this.nBlock = []; // number of blocks in the i-th Comp in a scan
    this.acTab = jpeg.lossless.Utils.createArray(10, 1); // ac HuffTab for the i-th Comp in a scan
    this.dcTab = jpeg.lossless.Utils.createArray(10, 1); // dc HuffTab for the i-th Comp in a scan
    this.qTab = jpeg.lossless.Utils.createArray(10, 1); // quantization table for the i-th Comp in a scan
    this.marker = 0;
    this.markerIndex = 0;
    this.numComp = 0;
    this.restartInterval = 0;
    this.selection = 0;
    this.xDim = 0;
    this.yDim = 0;
    this.xLoc = 0;
    this.yLoc = 0;
    this.numBytes = 0;
    this.outputData = null;
    this.restarting = false;
    this.mask = 0;

    if (typeof numBytes !== "undefined") {
        this.numBytes = numBytes;
    }
};


/*** Static Pseudo-constants ***/

jpeg.lossless.Decoder.IDCT_P = [0, 5, 40, 16, 45, 2, 7, 42, 21, 56, 8, 61, 18, 47, 1, 4, 41, 23, 58, 13, 32, 24, 37, 10, 63, 17, 44, 3, 6, 43, 20,
    57, 15, 34, 29, 48, 53, 26, 39, 9, 60, 19, 46, 22, 59, 12, 33, 31, 50, 55, 25, 36, 11, 62, 14, 35, 28, 49, 52, 27, 38, 30, 51, 54];
jpeg.lossless.Decoder.TABLE = [0, 1, 5, 6, 14, 15, 27, 28, 2, 4, 7, 13, 16, 26, 29, 42, 3, 8, 12, 17, 25, 30, 41, 43, 9, 11, 18, 24, 31, 40, 44, 53,
    10, 19, 23, 32, 39, 45, 52, 54, 20, 22, 33, 38, 46, 51, 55, 60, 21, 34, 37, 47, 50, 56, 59, 61, 35, 36, 48, 49, 57, 58, 62, 63];
jpeg.lossless.Decoder.MAX_HUFFMAN_SUBTREE = 50;
jpeg.lossless.Decoder.MSB = 0x80000000;
jpeg.lossless.Decoder.RESTART_MARKER_BEGIN = 0xFFD0;
jpeg.lossless.Decoder.RESTART_MARKER_END = 0xFFD7;

/*** Prototype Methods ***/

/**
 * Returns decompressed data.
 * @param {ArrayBuffer} buffer
 * @param {number} [offset]
 * @param {number} [length]
 * @returns {ArrayBufer}
 */
jpeg.lossless.Decoder.prototype.decompress = function (buffer, offset, length) {
    return this.decode(buffer, offset, length).buffer;
};



jpeg.lossless.Decoder.prototype.decode = function (buffer, offset, length, numBytes) {
    /*jslint bitwise: true */

    var current, scanNum = 0, pred = [], i, compN, temp = [], index = [], mcuNum;

    if (typeof buffer !== "undefined") {
        this.buffer = buffer;
    }

    if (typeof numBytes !== "undefined") {
        this.numBytes = numBytes;
    }

    this.stream = new jpeg.lossless.DataStream(this.buffer, offset, length);
    this.buffer = null;

    this.xLoc = 0;
    this.yLoc = 0;
    current = this.stream.get16();

    if (current !== 0xFFD8) { // SOI
        throw new Error("Not a JPEG file");
    }

    current = this.stream.get16();

    while ((((current >> 4) !== 0x0FFC) || (current === 0xFFC4))) { // SOF 0~15
        switch (current) {
            case 0xFFC4: // DHT
                this.huffTable.read(this.stream, this.HuffTab);
                break;
            case 0xFFCC: // DAC
                throw new Error("Program doesn't support arithmetic coding. (format throw new IOException)");
            case 0xFFDB:
                this.quantTable.read(this.stream, jpeg.lossless.Decoder.TABLE);
                break;
            case 0xFFDD:
                this.restartInterval = this.readNumber();
                break;
            case 0xFFE0:
            case 0xFFE1:
            case 0xFFE2:
            case 0xFFE3:
            case 0xFFE4:
            case 0xFFE5:
            case 0xFFE6:
            case 0xFFE7:
            case 0xFFE8:
            case 0xFFE9:
            case 0xFFEA:
            case 0xFFEB:
            case 0xFFEC:
            case 0xFFED:
            case 0xFFEE:
            case 0xFFEF:
                this.readApp();
                break;
            case 0xFFFE:
                this.readComment();
                break;
            default:
                if ((current >> 8) !== 0xFF) {
                    throw new Error("ERROR: format throw new IOException! (decode)");
                }
        }

        current = this.stream.get16();
    }

    if ((current < 0xFFC0) || (current > 0xFFC7)) {
        throw new Error("ERROR: could not handle arithmetic code!");
    }

    this.frame.read(this.stream);
    current = this.stream.get16();

    do {
        while (current !== 0x0FFDA) { // SOS
            switch (current) {
                case 0xFFC4: // DHT
                    this.huffTable.read(this.stream, this.HuffTab);
                    break;
                case 0xFFCC: // DAC
                    throw new Error("Program doesn't support arithmetic coding. (format throw new IOException)");
                case 0xFFDB:
                    this.quantTable.read(this.stream, jpeg.lossless.Decoder.TABLE);
                    break;
                case 0xFFDD:
                    this.restartInterval = this.readNumber();
                    break;
                case 0xFFE0:
                case 0xFFE1:
                case 0xFFE2:
                case 0xFFE3:
                case 0xFFE4:
                case 0xFFE5:
                case 0xFFE6:
                case 0xFFE7:
                case 0xFFE8:
                case 0xFFE9:
                case 0xFFEA:
                case 0xFFEB:
                case 0xFFEC:
                case 0xFFED:
                case 0xFFEE:
                case 0xFFEF:
                    this.readApp();
                    break;
                case 0xFFFE:
                    this.readComment();
                    break;
                default:
                    if ((current >> 8) !== 0xFF) {
                        throw new Error("ERROR: format throw new IOException! (Parser.decode)");
                    }
            }

            current = this.stream.get16();
        }

        this.precision = this.frame.precision;
        this.components = this.frame.components;

        if (!this.numBytes) {
            this.numBytes = parseInt(Math.ceil(this.precision / 8));
        }

        if (this.numBytes == 1) {
            this.mask = 0xFF;
        } else {
            this.mask = 0xFFFF;
        }

        this.scan.read(this.stream);
        this.numComp = this.scan.numComp;
        this.selection = this.scan.selection;

        if (this.numBytes === 1) {
            if (this.numComp === 3) {
                this.getter = this.getValueRGB;
                this.setter = this.setValueRGB;
                this.output = this.outputRGB;
            } else {
                this.getter = this.getValue8;
                this.setter = this.setValue8;
                this.output = this.outputSingle;
            }
        } else {
            this.getter = this.getValue16;
            this.setter = this.setValue16;
            this.output = this.outputSingle;
        }

        switch (this.selection) {
            case 2:
                this.selector = this.select2;
                break;
            case 3:
                this.selector = this.select3;
                break;
            case 4:
                this.selector = this.select4;
                break;
            case 5:
                this.selector = this.select5;
                break;
            case 6:
                this.selector = this.select6;
                break;
            case 7:
                this.selector = this.select7;
                break;
            default:
                this.selector = this.select1;
                break;
        }

        this.scanComps = this.scan.components;
        this.quantTables = this.quantTable.quantTables;

        for (i = 0; i < this.numComp; i+=1) {
            compN = this.scanComps[i].scanCompSel;
            this.qTab[i] = this.quantTables[this.components[compN].quantTableSel];
            this.nBlock[i] = this.components[compN].vSamp * this.components[compN].hSamp;
            this.dcTab[i] = this.HuffTab[this.scanComps[i].dcTabSel][0];
            this.acTab[i] = this.HuffTab[this.scanComps[i].acTabSel][1];
        }

        this.xDim = this.frame.dimX;
        this.yDim = this.frame.dimY;
        if (this.numBytes == 1) {
            this.outputData = new Uint8Array(new ArrayBuffer(this.xDim * this.yDim * this.numBytes * this.numComp));
        } else {
            this.outputData = new Uint16Array(new ArrayBuffer(this.xDim * this.yDim * this.numBytes * this.numComp));
        }

        scanNum+=1;

        while (true) { // Decode one scan
            temp[0] = 0;
            index[0] = 0;

            for (i = 0; i < 10; i+=1) {
                pred[i] = (1 << (this.precision - 1));
            }

            if (this.restartInterval === 0) {
                current = this.decodeUnit(pred, temp, index);

                while ((current === 0) && ((this.xLoc < this.xDim) && (this.yLoc < this.yDim))) {
                    this.output(pred);
                    current = this.decodeUnit(pred, temp, index);
                }

                break; //current=MARKER
            }

            for (mcuNum = 0; mcuNum < this.restartInterval; mcuNum+=1) {
                this.restarting = (mcuNum == 0);
                current = this.decodeUnit(pred, temp, index);
                this.output(pred);

                if (current !== 0) {
                    break;
                }
            }

            if (current === 0) {
                if (this.markerIndex !== 0) {
                    current = (0xFF00 | this.marker);
                    this.markerIndex = 0;
                } else {
                    current = this.stream.get16();
                }
            }

            if (!((current >= jpeg.lossless.Decoder.RESTART_MARKER_BEGIN) &&
                (current <= jpeg.lossless.Decoder.RESTART_MARKER_END))) {
                break; //current=MARKER
            }
        }

        if ((current === 0xFFDC) && (scanNum === 1)) { //DNL
            this.readNumber();
            current = this.stream.get16();
        }
    } while ((current !== 0xFFD9) && ((this.xLoc < this.xDim) && (this.yLoc < this.yDim)) && (scanNum === 0));

    return this.outputData;
};



jpeg.lossless.Decoder.prototype.decodeUnit = function (prev, temp, index) {
    if (this.numComp == 1) {
        return this.decodeSingle(prev, temp, index);
    } else if (this.numComp == 3) {
        return this.decodeRGB(prev, temp, index);
    } else {
        return -1;
    }
};



jpeg.lossless.Decoder.prototype.select1 = function (compOffset) {
    return this.getPreviousX(compOffset);
};



jpeg.lossless.Decoder.prototype.select2 = function (compOffset) {
    return this.getPreviousY(compOffset);
};



jpeg.lossless.Decoder.prototype.select3 = function (compOffset) {
    return this.getPreviousXY(compOffset);
};



jpeg.lossless.Decoder.prototype.select4 = function (compOffset) {
    return (this.getPreviousX(compOffset) + this.getPreviousY(compOffset)) - this.getPreviousXY(compOffset);
};



jpeg.lossless.Decoder.prototype.select5 = function (compOffset) {
    return this.getPreviousX(compOffset) + ((this.getPreviousY(compOffset) - this.getPreviousXY(compOffset)) >> 1);
};



jpeg.lossless.Decoder.prototype.select6 = function (compOffset) {
    return this.getPreviousY(compOffset) + ((this.getPreviousX(compOffset) - this.getPreviousXY(compOffset)) >> 1);
};



jpeg.lossless.Decoder.prototype.select7 = function (compOffset) {
    return ((this.getPreviousX(compOffset) + this.getPreviousY(compOffset)) / 2);
};



jpeg.lossless.Decoder.prototype.decodeRGB = function (prev, temp, index) {
    /*jslint bitwise: true */

    var value, actab, dctab, qtab, ctrC, i, k, j;

    prev[0] = this.selector(0);
    prev[1] = this.selector(1);
    prev[2] = this.selector(2);

    for (ctrC = 0; ctrC < this.numComp; ctrC+=1) {
        qtab = this.qTab[ctrC];
        actab = this.acTab[ctrC];
        dctab = this.dcTab[ctrC];
        for (i = 0; i < this.nBlock[ctrC]; i+=1) {
            for (k = 0; k < this.IDCT_Source.length; k+=1) {
                this.IDCT_Source[k] = 0;
            }

            value = this.getHuffmanValue(dctab, temp, index);

            if (value >= 0xFF00) {
                return value;
            }

            prev[ctrC] = this.IDCT_Source[0] = prev[ctrC] + this.getn(index, value, temp, index);
            this.IDCT_Source[0] *= qtab[0];

            for (j = 1; j < 64; j+=1) {
                value = this.getHuffmanValue(actab, temp, index);

                if (value >= 0xFF00) {
                    return value;
                }

                j += (value >> 4);

                if ((value & 0x0F) === 0) {
                    if ((value >> 4) === 0) {
                        break;
                    }
                } else {
                    this.IDCT_Source[jpeg.lossless.Decoder.IDCT_P[j]] = this.getn(index, value & 0x0F, temp, index) * qtab[j];
                }
            }
        }
    }

    return 0;
};



jpeg.lossless.Decoder.prototype.decodeSingle = function (prev, temp, index) {
    /*jslint bitwise: true */

    var value, i, n, nRestart;

    if (this.restarting) {
        this.restarting = false;
        prev[0] = (1 << (this.frame.precision - 1));
    } else {
        prev[0] = this.selector();
    }

    for (i = 0; i < this.nBlock[0]; i+=1) {
        value = this.getHuffmanValue(this.dcTab[0], temp, index);
        if (value >= 0xFF00) {
            return value;
        }

        n = this.getn(prev, value, temp, index);
        nRestart = (n >> 8);

        if ((nRestart >= jpeg.lossless.Decoder.RESTART_MARKER_BEGIN) && (nRestart <= jpeg.lossless.Decoder.RESTART_MARKER_END)) {
            return nRestart;
        }

        prev[0] += n;
    }

    return 0;
};



//	Huffman table for fast search: (HuffTab) 8-bit Look up table 2-layer search architecture, 1st-layer represent 256 node (8 bits) if codeword-length > 8
//	bits, then the entry of 1st-layer = (# of 2nd-layer table) | MSB and it is stored in the 2nd-layer Size of tables in each layer are 256.
//	HuffTab[*][*][0-256] is always the only 1st-layer table.
//
//	An entry can be: (1) (# of 2nd-layer table) | MSB , for code length > 8 in 1st-layer (2) (Code length) << 8 | HuffVal
//
//	HuffmanValue(table   HuffTab[x][y] (ex) HuffmanValue(HuffTab[1][0],...)
//	                ):
//	    return: Huffman Value of table
//	            0xFF?? if it receives a MARKER
//	    Parameter:  table   HuffTab[x][y] (ex) HuffmanValue(HuffTab[1][0],...)
//	                temp    temp storage for remainded bits
//	                index   index to bit of temp
//	                in      FILE pointer
//	    Effect:
//	        temp  store new remainded bits
//	        index change to new index
//	        in    change to new position
//	    NOTE:
//	      Initial by   temp=0; index=0;
//	    NOTE: (explain temp and index)
//	      temp: is always in the form at calling time or returning time
//	       |  byte 4  |  byte 3  |  byte 2  |  byte 1  |
//	       |     0    |     0    | 00000000 | 00000??? |  if not a MARKER
//	                                               ^index=3 (from 0 to 15)
//	                                               321
//	    NOTE (marker and marker_index):
//	      If get a MARKER from 'in', marker=the low-byte of the MARKER
//	        and marker_index=9
//	      If marker_index=9 then index is always > 8, or HuffmanValue()
//	        will not be called
jpeg.lossless.Decoder.prototype.getHuffmanValue = function (table, temp, index) {
    /*jslint bitwise: true */

    var code, input, mask;
    mask = 0xFFFF;

    if (index[0] < 8) {
        temp[0] <<= 8;
        input = this.stream.get8();
        if (input === 0xFF) {
            this.marker = this.stream.get8();
            if (this.marker !== 0) {
                this.markerIndex = 9;
            }
        }
        temp[0] |= input;
    } else {
        index[0] -= 8;
    }

    code = table[temp[0] >> index[0]];

    if ((code & jpeg.lossless.Decoder.MSB) !== 0) {
        if (this.markerIndex !== 0) {
            this.markerIndex = 0;
            return 0xFF00 | this.marker;
        }

        temp[0] &= (mask >> (16 - index[0]));
        temp[0] <<= 8;
        input = this.stream.get8();

        if (input === 0xFF) {
            this.marker = this.stream.get8();
            if (this.marker !== 0) {
                this.markerIndex = 9;
            }
        }

        temp[0] |= input;
        code = table[((code & 0xFF) * 256) + (temp[0] >> index[0])];
        index[0] += 8;
    }

    index[0] += 8 - (code >> 8);

    if (index[0] < 0) {
        throw new Error("index=" + index[0] + " temp=" + temp[0] + " code=" + code + " in HuffmanValue()");
    }

    if (index[0] < this.markerIndex) {
        this.markerIndex = 0;
        return 0xFF00 | this.marker;
    }

    temp[0] &= (mask >> (16 - index[0]));
    return code & 0xFF;
};



jpeg.lossless.Decoder.prototype.getn = function (PRED, n, temp, index) {
    /*jslint bitwise: true */

    var result, one, n_one, mask, input;
    one = 1;
    n_one = -1;
    mask = 0xFFFF;

    if (n === 0) {
        return 0;
    }

    if (n === 16) {
        if (PRED[0] >= 0) {
            return -32768;
        } else {
            return 32768;
        }
    }

    index[0] -= n;

    if (index[0] >= 0) {
        if ((index[0] < this.markerIndex) && !this.isLastPixel()) { // this was corrupting the last pixel in some cases
            this.markerIndex = 0;
            return (0xFF00 | this.marker) << 8;
        }

        result = temp[0] >> index[0];
        temp[0] &= (mask >> (16 - index[0]));
    } else {
        temp[0] <<= 8;
        input = this.stream.get8();

        if (input === 0xFF) {
            this.marker = this.stream.get8();
            if (this.marker !== 0) {
                this.markerIndex = 9;
            }
        }

        temp[0] |= input;
        index[0] += 8;

        if (index[0] < 0) {
            if (this.markerIndex !== 0) {
                this.markerIndex = 0;
                return (0xFF00 | this.marker) << 8;
            }

            temp[0] <<= 8;
            input = this.stream.get8();

            if (input === 0xFF) {
                this.marker = this.stream.get8();
                if (this.marker !== 0) {
                    this.markerIndex = 9;
                }
            }

            temp[0] |= input;
            index[0] += 8;
        }

        if (index[0] < 0) {
            throw new Error("index=" + index[0] + " in getn()");
        }

        if (index[0] < this.markerIndex) {
            this.markerIndex = 0;
            return (0xFF00 | this.marker) << 8;
        }

        result = temp[0] >> index[0];
        temp[0] &= (mask >> (16 - index[0]));
    }

    if (result < (one << (n - 1))) {
        result += (n_one << n) + 1;
    }

    return result;
};



jpeg.lossless.Decoder.prototype.getPreviousX = function (compOffset) {
    /*jslint bitwise: true */

    if (this.xLoc > 0) {
        return this.getter((((this.yLoc * this.xDim) + this.xLoc) - 1), compOffset);
    } else if (this.yLoc > 0) {
        return this.getPreviousY(compOffset);
    } else {
        return (1 << (this.frame.precision - 1));
    }
};



jpeg.lossless.Decoder.prototype.getPreviousXY = function (compOffset) {
    /*jslint bitwise: true */

    if ((this.xLoc > 0) && (this.yLoc > 0)) {
        return this.getter(((((this.yLoc - 1) * this.xDim) + this.xLoc) - 1), compOffset);
    } else {
        return this.getPreviousY(compOffset);
    }
};



jpeg.lossless.Decoder.prototype.getPreviousY = function (compOffset) {
    /*jslint bitwise: true */

    if (this.yLoc > 0) {
        return this.getter((((this.yLoc - 1) * this.xDim) + this.xLoc), compOffset);
    } else {
        return this.getPreviousX(compOffset);
    }
};



jpeg.lossless.Decoder.prototype.isLastPixel = function () {
    return (this.xLoc === (this.xDim - 1)) && (this.yLoc === (this.yDim - 1));
};



jpeg.lossless.Decoder.prototype.outputSingle = function (PRED) {
    if ((this.xLoc < this.xDim) && (this.yLoc < this.yDim)) {
        this.setter((((this.yLoc * this.xDim) + this.xLoc)), this.mask & PRED[0]);

        this.xLoc+=1;

        if (this.xLoc >= this.xDim) {
            this.yLoc+=1;
            this.xLoc = 0;
        }
    }
};



jpeg.lossless.Decoder.prototype.outputRGB = function (PRED) {
    var offset = ((this.yLoc * this.xDim) + this.xLoc);

    if ((this.xLoc < this.xDim) && (this.yLoc < this.yDim)) {
        this.setter(offset, PRED[0], 0);
        this.setter(offset, PRED[1], 1);
        this.setter(offset, PRED[2], 2);

        this.xLoc+=1;

        if (this.xLoc >= this.xDim) {
            this.yLoc+=1;
            this.xLoc = 0;
        }
    }
};

jpeg.lossless.Decoder.prototype.setValue8 = function (index, val) {
    this.outputData[index] = val; 
};

jpeg.lossless.Decoder.prototype.getValue8 = function (index) {
    return this.outputData[index]; // mask should not be necessary because outputData is either Int8Array or Int16Array
};

var littleEndian = (function() {
    var buffer = new ArrayBuffer(2);
    new DataView(buffer).setInt16(0, 256, true /* littleEndian */);
    // Int16Array uses the platform's endianness.
    return new Int16Array(buffer)[0] === 256;
})();

if (littleEndian) {
    // just reading from an array is fine then. Int16Array will use platform endianness.
    jpeg.lossless.Decoder.prototype.setValue16 = jpeg.lossless.Decoder.prototype.setValue8; 
    jpeg.lossless.Decoder.prototype.getValue16 = jpeg.lossless.Decoder.prototype.getValue8;
} 
else {
    // If platform is big-endian, we will need to convert to little-endian 
    jpeg.lossless.Decoder.prototype.setValue16 = function (index, val) {
        this.outputData[index] = ((val & 0xFF) << 8) | ((val >> 8) & 0xFF); 
    };

    jpeg.lossless.Decoder.prototype.getValue16 = function (index) {
        var val = this.outputData[index];
        return ((val & 0xFF) << 8) | ((val >> 8) & 0xFF);
    };
}

jpeg.lossless.Decoder.prototype.setValueRGB = function (index, val, compOffset) {
    // this.outputData.setUint8(index * 3 + compOffset, val);
    this.outputData[index * 3 + compOffset] = val;
};

jpeg.lossless.Decoder.prototype.getValueRGB = function (index, compOffset) {
    // return this.outputData.getUint8(index * 3 + compOffset);
    return this.outputData[index * 3 + compOffset];
};



jpeg.lossless.Decoder.prototype.readApp = function() {
    var count = 0, length = this.stream.get16();
    count += 2;

    while (count < length) {
        this.stream.get8();
        count+=1;
    }

    return length;
};



jpeg.lossless.Decoder.prototype.readComment = function () {
    var sb = "", count = 0, length;

    length = this.stream.get16();
    count += 2;

    while (count < length) {
        sb += this.stream.get8();
        count+=1;
    }

    return sb;
};



jpeg.lossless.Decoder.prototype.readNumber = function() {
    var Ld = this.stream.get16();

    if (Ld !== 4) {
        throw new Error("ERROR: Define number format throw new IOException [Ld!=4]");
    }

    return this.stream.get16();
};



/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.Decoder;
}

},{"./data-stream.js":2,"./frame-header.js":4,"./huffman-table.js":5,"./quantization-table.js":7,"./scan-header.js":9,"./utils.js":10}],4:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};
jpeg.lossless.ComponentSpec = jpeg.lossless.ComponentSpec || ((typeof require !== 'undefined') ? require('./component-spec.js') : null);
jpeg.lossless.DataStream = jpeg.lossless.DataStream || ((typeof require !== 'undefined') ? require('./data-stream.js') : null);


/*** Constructor ***/
jpeg.lossless.FrameHeader = jpeg.lossless.FrameHeader || function () {
    this.components = []; // Components
    this.dimX = 0; // Number of samples per line
    this.dimY = 0; // Number of lines
    this.numComp = 0; // Number of component in the frame
    this.precision = 0; // Sample Precision (from the original image)
};



/*** Prototype Methods ***/

jpeg.lossless.FrameHeader.prototype.read = function (data) {
    /*jslint bitwise: true */

    var count = 0, length, i, c, temp;

    length = data.get16();
    count += 2;

    this.precision = data.get8();
    count+=1;

    this.dimY = data.get16();
    count += 2;

    this.dimX = data.get16();
    count += 2;

    this.numComp = data.get8();
    count+=1;
    for (i = 1; i <= this.numComp; i+=1) {
        if (count > length) {
            throw new Error("ERROR: frame format error");
        }

        c = data.get8();
        count+=1;

        if (count >= length) {
            throw new Error("ERROR: frame format error [c>=Lf]");
        }

        temp = data.get8();
        count+=1;

        if (!this.components[c]) {
            this.components[c] = new jpeg.lossless.ComponentSpec();
        }

        this.components[c].hSamp = temp >> 4;
        this.components[c].vSamp = temp & 0x0F;
        this.components[c].quantTableSel = data.get8();
        count+=1;
    }

    if (count !== length) {
        throw new Error("ERROR: frame format error [Lf!=count]");
    }

    return 1;
};


/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.FrameHeader;
}

},{"./component-spec.js":1,"./data-stream.js":2}],5:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};
jpeg.lossless.DataStream = jpeg.lossless.DataStream || ((typeof require !== 'undefined') ? require('./data-stream.js') : null);
jpeg.lossless.Utils = jpeg.lossless.Utils || ((typeof require !== 'undefined') ? require('./utils.js') : null);


/*** Constructor ***/
jpeg.lossless.HuffmanTable = jpeg.lossless.HuffmanTable || function () {
    this.l = jpeg.lossless.Utils.createArray(4, 2, 16);
    this.th = [];
    this.v = jpeg.lossless.Utils.createArray(4, 2, 16, 200);
    this.tc = jpeg.lossless.Utils.createArray(4, 2);

    this.tc[0][0] = 0;
    this.tc[1][0] = 0;
    this.tc[2][0] = 0;
    this.tc[3][0] = 0;
    this.tc[0][1] = 0;
    this.tc[1][1] = 0;
    this.tc[2][1] = 0;
    this.tc[3][1] = 0;
    this.th[0] = 0;
    this.th[1] = 0;
    this.th[2] = 0;
    this.th[3] = 0;
};



/*** Static Pseudo-constants ***/

jpeg.lossless.HuffmanTable.MSB = 0x80000000;


/*** Prototype Methods ***/

jpeg.lossless.HuffmanTable.prototype.read = function(data, HuffTab) {
    /*jslint bitwise: true */

    var count = 0, length, temp, t, c, i, j;

    length = data.get16();
    count += 2;

    while (count < length) {
        temp = data.get8();
        count+=1;
        t = temp & 0x0F;
        if (t > 3) {
            throw new Error("ERROR: Huffman table ID > 3");
        }

        c = temp >> 4;
        if (c > 2) {
            throw new Error("ERROR: Huffman table [Table class > 2 ]");
        }

        this.th[t] = 1;
        this.tc[t][c] = 1;

        for (i = 0; i < 16; i+=1) {
            this.l[t][c][i] = data.get8();
            count+=1;
        }

        for (i = 0; i < 16; i+=1) {
            for (j = 0; j < this.l[t][c][i]; j+=1) {
                if (count > length) {
                    throw new Error("ERROR: Huffman table format error [count>Lh]");
                }

                this.v[t][c][i][j] = data.get8();
                count+=1;
            }
        }
    }

    if (count !== length) {
        throw new Error("ERROR: Huffman table format error [count!=Lf]");
    }

    for (i = 0; i < 4; i+=1) {
        for (j = 0; j < 2; j+=1) {
            if (this.tc[i][j] !== 0) {
                this.buildHuffTable(HuffTab[i][j], this.l[i][j], this.v[i][j]);
            }
        }
    }

    return 1;
};



//	Build_HuffTab()
//	Parameter:  t       table ID
//	            c       table class ( 0 for DC, 1 for AC )
//	            L[i]    # of codewords which length is i
//	            V[i][j] Huffman Value (length=i)
//	Effect:
//	    build up HuffTab[t][c] using L and V.
jpeg.lossless.HuffmanTable.prototype.buildHuffTable = function(tab, L, V) {
    /*jslint bitwise: true */

    var currentTable, temp, k, i, j, n;
    temp = 256;
    k = 0;

    for (i = 0; i < 8; i+=1) { // i+1 is Code length
        for (j = 0; j < L[i]; j+=1) {
            for (n = 0; n < (temp >> (i + 1)); n+=1) {
                tab[k] = V[i][j] | ((i + 1) << 8);
                k+=1;
            }
        }
    }

    for (i = 1; k < 256; i+=1, k+=1) {
        tab[k] = i | jpeg.lossless.HuffmanTable.MSB;
    }

    currentTable = 1;
    k = 0;

    for (i = 8; i < 16; i+=1) { // i+1 is Code length
        for (j = 0; j < L[i]; j+=1) {
            for (n = 0; n < (temp >> (i - 7)); n+=1) {
                tab[(currentTable * 256) + k] = V[i][j] | ((i + 1) << 8);
                k+=1;
            }

            if (k >= 256) {
                if (k > 256) {
                    throw new Error("ERROR: Huffman table error(1)!");
                }

                k = 0;
                currentTable+=1;
            }
        }
    }
};


/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.HuffmanTable;
}

},{"./data-stream.js":2,"./utils.js":10}],6:[function(require,module,exports){
/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ****/

/**
 * jpeg
  * @type {*|{}}
 */
var jpeg = jpeg || {};

/**
 * jpeg.lossless
 * @type {*|{}}
 */
jpeg.lossless = jpeg.lossless || {};


jpeg.lossless.ComponentSpec = jpeg.lossless.ComponentSpec || ((typeof require !== 'undefined') ? require('./component-spec.js') : null);
jpeg.lossless.DataStream = jpeg.lossless.DataStream || ((typeof require !== 'undefined') ? require('./data-stream.js') : null);
jpeg.lossless.Decoder = jpeg.lossless.Decoder || ((typeof require !== 'undefined') ? require('./decoder.js') : null);
jpeg.lossless.FrameHeader = jpeg.lossless.FrameHeader || ((typeof require !== 'undefined') ? require('./frame-header.js') : null);
jpeg.lossless.HuffmanTable = jpeg.lossless.HuffmanTable || ((typeof require !== 'undefined') ? require('./huffman-table.js') : null);
jpeg.lossless.QuantizationTable = jpeg.lossless.QuantizationTable || ((typeof require !== 'undefined') ? require('./quantization-table.js') : null);
jpeg.lossless.ScanComponent = jpeg.lossless.ScanComponent || ((typeof require !== 'undefined') ? require('./scan-component.js') : null);
jpeg.lossless.ScanHeader = jpeg.lossless.ScanHeader || ((typeof require !== 'undefined') ? require('./scan-header.js') : null);
jpeg.lossless.Utils = jpeg.lossless.Utils || ((typeof require !== 'undefined') ? require('./utils.js') : null);


/*** Exports ***/
var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg;
}

},{"./component-spec.js":1,"./data-stream.js":2,"./decoder.js":3,"./frame-header.js":4,"./huffman-table.js":5,"./quantization-table.js":7,"./scan-component.js":8,"./scan-header.js":9,"./utils.js":10}],7:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};
jpeg.lossless.DataStream = jpeg.lossless.DataStream || ((typeof require !== 'undefined') ? require('./data-stream.js') : null);
jpeg.lossless.Utils = jpeg.lossless.Utils || ((typeof require !== 'undefined') ? require('./utils.js') : null);


/*** Constructor ***/
jpeg.lossless.QuantizationTable = jpeg.lossless.QuantizationTable || function () {
    this.precision = []; // Quantization precision 8 or 16
    this.tq = []; // 1: this table is presented
    this.quantTables = jpeg.lossless.Utils.createArray(4, 64); // Tables

    this.tq[0] = 0;
    this.tq[1] = 0;
    this.tq[2] = 0;
    this.tq[3] = 0;
};



/*** Static Methods ***/

jpeg.lossless.QuantizationTable.enhanceQuantizationTable = function(qtab, table) {
    /*jslint bitwise: true */

    var i;

    for (i = 0; i < 8; i+=1) {
        qtab[table[(0 * 8) + i]] *= 90;
        qtab[table[(4 * 8) + i]] *= 90;
        qtab[table[(2 * 8) + i]] *= 118;
        qtab[table[(6 * 8) + i]] *= 49;
        qtab[table[(5 * 8) + i]] *= 71;
        qtab[table[(1 * 8) + i]] *= 126;
        qtab[table[(7 * 8) + i]] *= 25;
        qtab[table[(3 * 8) + i]] *= 106;
    }

    for (i = 0; i < 8; i+=1) {
        qtab[table[0 + (8 * i)]] *= 90;
        qtab[table[4 + (8 * i)]] *= 90;
        qtab[table[2 + (8 * i)]] *= 118;
        qtab[table[6 + (8 * i)]] *= 49;
        qtab[table[5 + (8 * i)]] *= 71;
        qtab[table[1 + (8 * i)]] *= 126;
        qtab[table[7 + (8 * i)]] *= 25;
        qtab[table[3 + (8 * i)]] *= 106;
    }

    for (i = 0; i < 64; i+=1) {
        qtab[i] >>= 6;
    }
};


/*** Prototype Methods ***/

jpeg.lossless.QuantizationTable.prototype.read = function (data, table) {
    /*jslint bitwise: true */

    var count = 0, length, temp, t, i;

    length = data.get16();
    count += 2;

    while (count < length) {
        temp = data.get8();
        count+=1;
        t = temp & 0x0F;

        if (t > 3) {
            throw new Error("ERROR: Quantization table ID > 3");
        }

        this.precision[t] = temp >> 4;

        if (this.precision[t] === 0) {
            this.precision[t] = 8;
        } else if (this.precision[t] === 1) {
            this.precision[t] = 16;
        } else {
            throw new Error("ERROR: Quantization table precision error");
        }

        this.tq[t] = 1;

        if (this.precision[t] === 8) {
            for (i = 0; i < 64; i+=1) {
                if (count > length) {
                    throw new Error("ERROR: Quantization table format error");
                }

                this.quantTables[t][i] = data.get8();
                count+=1;
            }

            jpeg.lossless.QuantizationTable.enhanceQuantizationTable(this.quantTables[t], table);
        } else {
            for (i = 0; i < 64; i+=1) {
                if (count > length) {
                    throw new Error("ERROR: Quantization table format error");
                }

                this.quantTables[t][i] = data.get16();
                count += 2;
            }

            jpeg.lossless.QuantizationTable.enhanceQuantizationTable(this.quantTables[t], table);
        }
    }

    if (count !== length) {
        throw new Error("ERROR: Quantization table error [count!=Lq]");
    }

    return 1;
};



/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.QuantizationTable;
}

},{"./data-stream.js":2,"./utils.js":10}],8:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};


/*** Constructor ***/
jpeg.lossless.ScanComponent = jpeg.lossless.ScanComponent || function () {
    this.acTabSel = 0; // AC table selector
    this.dcTabSel = 0; // DC table selector
    this.scanCompSel = 0; // Scan component selector
};



/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.ScanComponent;
}

},{}],9:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};
jpeg.lossless.DataStream = jpeg.lossless.DataStream || ((typeof require !== 'undefined') ? require('./data-stream.js') : null);
jpeg.lossless.ScanComponent = jpeg.lossless.ScanComponent || ((typeof require !== 'undefined') ? require('./scan-component.js') : null);


/*** Constructor ***/
jpeg.lossless.ScanHeader = jpeg.lossless.ScanHeader || function () {
    this.ah = 0;
    this.al = 0;
    this.numComp = 0; // Number of components in the scan
    this.selection = 0; // Start of spectral or predictor selection
    this.spectralEnd = 0; // End of spectral selection
    this.components = [];
};


/*** Prototype Methods ***/

jpeg.lossless.ScanHeader.prototype.read = function(data) {
    /*jslint bitwise: true */

    var count = 0, length, i, temp;

    length = data.get16();
    count += 2;

    this.numComp = data.get8();
    count+=1;

    for (i = 0; i < this.numComp; i+=1) {
        this.components[i] = new jpeg.lossless.ScanComponent();

        if (count > length) {
            throw new Error("ERROR: scan header format error");
        }

        this.components[i].scanCompSel = data.get8();
        count+=1;

        temp = data.get8();
        count+=1;

        this.components[i].dcTabSel = (temp >> 4);
        this.components[i].acTabSel = (temp & 0x0F);
    }

    this.selection = data.get8();
    count+=1;

    this.spectralEnd = data.get8();
    count+=1;

    temp = data.get8();
    this.ah = (temp >> 4);
    this.al = (temp & 0x0F);
    count+=1;

    if (count !== length) {
        throw new Error("ERROR: scan header format error [count!=Ns]");
    }

    return 1;
};



/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.ScanHeader;
}

},{"./data-stream.js":2,"./scan-component.js":8}],10:[function(require,module,exports){
/*
 * Copyright (C) 2015 Michael Martinez
 * Changes: Added support for selection values 2-7, fixed minor bugs &
 * warnings, split into multiple class files, and general clean up.
 *
 * 08-25-2015: Helmut Dersch agreed to a license change from LGPL to MIT.
 */

/*
 * Copyright (C) Helmut Dersch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint browser: true, node: true */
/*global require, module */

"use strict";

/*** Imports ***/
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};


/*** Constructor ***/
jpeg.lossless.Utils = jpeg.lossless.Utils || {};


/*** Static methods ***/

// http://stackoverflow.com/questions/966225/how-can-i-create-a-two-dimensional-array-in-javascript
jpeg.lossless.Utils.createArray = function (length) {
    var arr = new Array(length || 0),
        i = length;

    if (arguments.length > 1) {
        var args = Array.prototype.slice.call(arguments, 1);
        while(i--) arr[length-1 - i] = jpeg.lossless.Utils.createArray.apply(this, args);
    }

    return arr;
};


// http://stackoverflow.com/questions/18638900/javascript-crc32
jpeg.lossless.Utils.makeCRCTable = function(){
    var c;
    var crcTable = [];
    for(var n =0; n < 256; n++){
        c = n;
        for(var k =0; k < 8; k++){
            c = ((c&1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1));
        }
        crcTable[n] = c;
    }
    return crcTable;
};

jpeg.lossless.Utils.crc32 = function(dataView) {
    var uint8view = new Uint8Array(dataView.buffer);
    var crcTable = jpeg.lossless.Utils.crcTable || (jpeg.lossless.Utils.crcTable = jpeg.lossless.Utils.makeCRCTable());
    var crc = 0 ^ (-1);

    for (var i = 0; i < uint8view.length; i++ ) {
        crc = (crc >>> 8) ^ crcTable[(crc ^ uint8view[i]) & 0xFF];
    }

    return (crc ^ (-1)) >>> 0;
};


/*** Exports ***/

var moduleType = typeof module;
if ((moduleType !== 'undefined') && module.exports) {
    module.exports = jpeg.lossless.Utils;
}

},{}]},{},[6])(6)
});