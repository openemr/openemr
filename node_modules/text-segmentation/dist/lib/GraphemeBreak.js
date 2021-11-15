"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.splitGraphemes = exports.GraphemeBreaker = exports.graphemeBreakAtIndex = exports.codePointToClass = exports.BREAK_ALLOWED = exports.BREAK_NOT_ALLOWED = exports.UnicodeTrie = exports.fromCodePoint = exports.toCodePoints = exports.classes = void 0;
var grapheme_break_trie_1 = require("./grapheme-break-trie");
var utrie_1 = require("utrie");
var Other = 0;
var Prepend = 1;
var CR = 2;
var LF = 3;
var Control = 4;
var Extend = 5;
var Regional_Indicator = 6;
var SpacingMark = 7;
var L = 8;
var V = 9;
var T = 10;
var LV = 11;
var LVT = 12;
var ZWJ = 13;
var Extended_Pictographic = 14;
var RI = 15;
exports.classes = {
    Other: Other,
    Prepend: Prepend,
    CR: CR,
    LF: LF,
    Control: Control,
    Extend: Extend,
    Regional_Indicator: Regional_Indicator,
    SpacingMark: SpacingMark,
    L: L,
    V: V,
    T: T,
    LV: LV,
    LVT: LVT,
    ZWJ: ZWJ,
    Extended_Pictographic: Extended_Pictographic,
    RI: RI,
};
var toCodePoints = function (str) {
    var codePoints = [];
    var i = 0;
    var length = str.length;
    while (i < length) {
        var value = str.charCodeAt(i++);
        if (value >= 0xd800 && value <= 0xdbff && i < length) {
            var extra = str.charCodeAt(i++);
            if ((extra & 0xfc00) === 0xdc00) {
                codePoints.push(((value & 0x3ff) << 10) + (extra & 0x3ff) + 0x10000);
            }
            else {
                codePoints.push(value);
                i--;
            }
        }
        else {
            codePoints.push(value);
        }
    }
    return codePoints;
};
exports.toCodePoints = toCodePoints;
var fromCodePoint = function () {
    var codePoints = [];
    for (var _i = 0; _i < arguments.length; _i++) {
        codePoints[_i] = arguments[_i];
    }
    if (String.fromCodePoint) {
        return String.fromCodePoint.apply(String, codePoints);
    }
    var length = codePoints.length;
    if (!length) {
        return '';
    }
    var codeUnits = [];
    var index = -1;
    var result = '';
    while (++index < length) {
        var codePoint = codePoints[index];
        if (codePoint <= 0xffff) {
            codeUnits.push(codePoint);
        }
        else {
            codePoint -= 0x10000;
            codeUnits.push((codePoint >> 10) + 0xd800, (codePoint % 0x400) + 0xdc00);
        }
        if (index + 1 === length || codeUnits.length > 0x4000) {
            result += String.fromCharCode.apply(String, codeUnits);
            codeUnits.length = 0;
        }
    }
    return result;
};
exports.fromCodePoint = fromCodePoint;
exports.UnicodeTrie = utrie_1.createTrieFromBase64(grapheme_break_trie_1.base64, grapheme_break_trie_1.byteLength);
exports.BREAK_NOT_ALLOWED = '×';
exports.BREAK_ALLOWED = '÷';
var codePointToClass = function (codePoint) { return exports.UnicodeTrie.get(codePoint); };
exports.codePointToClass = codePointToClass;
var _graphemeBreakAtIndex = function (_codePoints, classTypes, index) {
    var prevIndex = index - 2;
    var prev = classTypes[prevIndex];
    var current = classTypes[index - 1];
    var next = classTypes[index];
    // GB3 Do not break between a CR and LF
    if (current === CR && next === LF) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // GB4 Otherwise, break before and after controls.
    if (current === CR || current === LF || current === Control) {
        return exports.BREAK_ALLOWED;
    }
    // GB5
    if (next === CR || next === LF || next === Control) {
        return exports.BREAK_ALLOWED;
    }
    // Do not break Hangul syllable sequences.
    // GB6
    if (current === L && [L, V, LV, LVT].indexOf(next) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // GB7
    if ((current === LV || current === V) && (next === V || next === T)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // GB8
    if ((current === LVT || current === T) && next === T) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // GB9 Do not break before extending characters or ZWJ.
    if (next === ZWJ || next === Extend) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // Do not break before SpacingMarks, or after Prepend characters.
    // GB9a
    if (next === SpacingMark) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // GB9a
    if (current === Prepend) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // GB11 Do not break within emoji modifier sequences or emoji zwj sequences.
    if (current === ZWJ && next === Extended_Pictographic) {
        while (prev === Extend) {
            prev = classTypes[--prevIndex];
        }
        if (prev === Extended_Pictographic) {
            return exports.BREAK_NOT_ALLOWED;
        }
    }
    // GB12 Do not break within emoji flag sequences.
    // That is, do not break between regional indicator (RI) symbols
    // if there is an odd number of RI characters before the break point.
    if (current === RI && next === RI) {
        var countRI = 0;
        while (prev === RI) {
            countRI++;
            prev = classTypes[--prevIndex];
        }
        if (countRI % 2 === 0) {
            return exports.BREAK_NOT_ALLOWED;
        }
    }
    return exports.BREAK_ALLOWED;
};
var graphemeBreakAtIndex = function (codePoints, index) {
    // GB1 Break at the start and end of text, unless the text is empty.
    if (index === 0) {
        return exports.BREAK_ALLOWED;
    }
    // GB2
    if (index >= codePoints.length) {
        return exports.BREAK_ALLOWED;
    }
    var classTypes = codePoints.map(exports.codePointToClass);
    return _graphemeBreakAtIndex(codePoints, classTypes, index);
};
exports.graphemeBreakAtIndex = graphemeBreakAtIndex;
var GraphemeBreaker = function (str) {
    var codePoints = exports.toCodePoints(str);
    var length = codePoints.length;
    var index = 0;
    var lastEnd = 0;
    var classTypes = codePoints.map(exports.codePointToClass);
    return {
        next: function () {
            if (index >= length) {
                return { done: true, value: null };
            }
            var graphemeBreak = exports.BREAK_NOT_ALLOWED;
            while (index < length &&
                (graphemeBreak = _graphemeBreakAtIndex(codePoints, classTypes, ++index)) === exports.BREAK_NOT_ALLOWED) { }
            if (graphemeBreak !== exports.BREAK_NOT_ALLOWED || index === length) {
                var value = exports.fromCodePoint.apply(null, codePoints.slice(lastEnd, index));
                lastEnd = index;
                return { value: value, done: false };
            }
            return { done: true, value: null };
            while (index < length) { }
            return { done: true, value: null };
        },
    };
};
exports.GraphemeBreaker = GraphemeBreaker;
var splitGraphemes = function (str) {
    var breaker = exports.GraphemeBreaker(str);
    var graphemes = [];
    var bk;
    while (!(bk = breaker.next()).done) {
        if (bk.value) {
            graphemes.push(bk.value.slice());
        }
    }
    return graphemes;
};
exports.splitGraphemes = splitGraphemes;
//# sourceMappingURL=GraphemeBreak.js.map