/* @flow */
'use strict';
Object.defineProperty(exports, "__esModule", { value: true });
exports.LineBreaker = exports.inlineBreakOpportunities = exports.lineBreakAtIndex = exports.codePointsToCharacterClasses = exports.UnicodeTrie = exports.BREAK_ALLOWED = exports.BREAK_NOT_ALLOWED = exports.BREAK_MANDATORY = exports.classes = exports.LETTER_NUMBER_MODIFIER = void 0;
var Trie_1 = require("./Trie");
var linebreak_trie_1 = require("./linebreak-trie");
var Util_1 = require("./Util");
exports.LETTER_NUMBER_MODIFIER = 50;
// Non-tailorable Line Breaking Classes
var BK = 1; //  Cause a line break (after)
var CR = 2; //  Cause a line break (after), except between CR and LF
var LF = 3; //  Cause a line break (after)
var CM = 4; //  Prohibit a line break between the character and the preceding character
var NL = 5; //  Cause a line break (after)
var SG = 6; //  Do not occur in well-formed text
var WJ = 7; //  Prohibit line breaks before and after
var ZW = 8; //  Provide a break opportunity
var GL = 9; //  Prohibit line breaks before and after
var SP = 10; // Enable indirect line breaks
var ZWJ = 11; // Prohibit line breaks within joiner sequences
// Break Opportunities
var B2 = 12; //  Provide a line break opportunity before and after the character
var BA = 13; //  Generally provide a line break opportunity after the character
var BB = 14; //  Generally provide a line break opportunity before the character
var HY = 15; //  Provide a line break opportunity after the character, except in numeric context
var CB = 16; //   Provide a line break opportunity contingent on additional information
// Characters Prohibiting Certain Breaks
var CL = 17; //  Prohibit line breaks before
var CP = 18; //  Prohibit line breaks before
var EX = 19; //  Prohibit line breaks before
var IN = 20; //  Allow only indirect line breaks between pairs
var NS = 21; //  Allow only indirect line breaks before
var OP = 22; //  Prohibit line breaks after
var QU = 23; //  Act like they are both opening and closing
// Numeric Context
var IS = 24; //  Prevent breaks after any and before numeric
var NU = 25; //  Form numeric expressions for line breaking purposes
var PO = 26; //  Do not break following a numeric expression
var PR = 27; //  Do not break in front of a numeric expression
var SY = 28; //  Prevent a break before; and allow a break after
// Other Characters
var AI = 29; //  Act like AL when the resolvedEAW is N; otherwise; act as ID
var AL = 30; //  Are alphabetic characters or symbols that are used with alphabetic characters
var CJ = 31; //  Treat as NS or ID for strict or normal breaking.
var EB = 32; //  Do not break from following Emoji Modifier
var EM = 33; //  Do not break from preceding Emoji Base
var H2 = 34; //  Form Korean syllable blocks
var H3 = 35; //  Form Korean syllable blocks
var HL = 36; //  Do not break around a following hyphen; otherwise act as Alphabetic
var ID = 37; //  Break before or after; except in some numeric context
var JL = 38; //  Form Korean syllable blocks
var JV = 39; //  Form Korean syllable blocks
var JT = 40; //  Form Korean syllable blocks
var RI = 41; //  Keep pairs together. For pairs; break before and after other classes
var SA = 42; //  Provide a line break opportunity contingent on additional, language-specific context analysis
var XX = 43; //  Have as yet unknown line breaking behavior or unassigned code positions
var ea_OP = [0x2329, 0xff08];
exports.classes = {
    BK: BK,
    CR: CR,
    LF: LF,
    CM: CM,
    NL: NL,
    SG: SG,
    WJ: WJ,
    ZW: ZW,
    GL: GL,
    SP: SP,
    ZWJ: ZWJ,
    B2: B2,
    BA: BA,
    BB: BB,
    HY: HY,
    CB: CB,
    CL: CL,
    CP: CP,
    EX: EX,
    IN: IN,
    NS: NS,
    OP: OP,
    QU: QU,
    IS: IS,
    NU: NU,
    PO: PO,
    PR: PR,
    SY: SY,
    AI: AI,
    AL: AL,
    CJ: CJ,
    EB: EB,
    EM: EM,
    H2: H2,
    H3: H3,
    HL: HL,
    ID: ID,
    JL: JL,
    JV: JV,
    JT: JT,
    RI: RI,
    SA: SA,
    XX: XX,
};
exports.BREAK_MANDATORY = '!';
exports.BREAK_NOT_ALLOWED = '×';
exports.BREAK_ALLOWED = '÷';
exports.UnicodeTrie = Trie_1.createTrieFromBase64(linebreak_trie_1.base64);
var ALPHABETICS = [AL, HL];
var HARD_LINE_BREAKS = [BK, CR, LF, NL];
var SPACE = [SP, ZW];
var PREFIX_POSTFIX = [PR, PO];
var LINE_BREAKS = HARD_LINE_BREAKS.concat(SPACE);
var KOREAN_SYLLABLE_BLOCK = [JL, JV, JT, H2, H3];
var HYPHEN = [HY, BA];
var codePointsToCharacterClasses = function (codePoints, lineBreak) {
    if (lineBreak === void 0) { lineBreak = 'strict'; }
    var types = [];
    var indices = [];
    var categories = [];
    codePoints.forEach(function (codePoint, index) {
        var classType = exports.UnicodeTrie.get(codePoint);
        if (classType > exports.LETTER_NUMBER_MODIFIER) {
            categories.push(true);
            classType -= exports.LETTER_NUMBER_MODIFIER;
        }
        else {
            categories.push(false);
        }
        if (['normal', 'auto', 'loose'].indexOf(lineBreak) !== -1) {
            // U+2010, – U+2013, 〜 U+301C, ゠ U+30A0
            if ([0x2010, 0x2013, 0x301c, 0x30a0].indexOf(codePoint) !== -1) {
                indices.push(index);
                return types.push(CB);
            }
        }
        if (classType === CM || classType === ZWJ) {
            // LB10 Treat any remaining combining mark or ZWJ as AL.
            if (index === 0) {
                indices.push(index);
                return types.push(AL);
            }
            // LB9 Do not break a combining character sequence; treat it as if it has the line breaking class of
            // the base character in all of the following rules. Treat ZWJ as if it were CM.
            var prev = types[index - 1];
            if (LINE_BREAKS.indexOf(prev) === -1) {
                indices.push(indices[index - 1]);
                return types.push(prev);
            }
            indices.push(index);
            return types.push(AL);
        }
        indices.push(index);
        if (classType === CJ) {
            return types.push(lineBreak === 'strict' ? NS : ID);
        }
        if (classType === SA) {
            return types.push(AL);
        }
        if (classType === AI) {
            return types.push(AL);
        }
        // For supplementary characters, a useful default is to treat characters in the range 10000..1FFFD as AL
        // and characters in the ranges 20000..2FFFD and 30000..3FFFD as ID, until the implementation can be revised
        // to take into account the actual line breaking properties for these characters.
        if (classType === XX) {
            if ((codePoint >= 0x20000 && codePoint <= 0x2fffd) || (codePoint >= 0x30000 && codePoint <= 0x3fffd)) {
                return types.push(ID);
            }
            else {
                return types.push(AL);
            }
        }
        types.push(classType);
    });
    return [indices, types, categories];
};
exports.codePointsToCharacterClasses = codePointsToCharacterClasses;
var isAdjacentWithSpaceIgnored = function (a, b, currentIndex, classTypes) {
    var current = classTypes[currentIndex];
    if (Array.isArray(a) ? a.indexOf(current) !== -1 : a === current) {
        var i = currentIndex;
        while (i <= classTypes.length) {
            i++;
            var next = classTypes[i];
            if (next === b) {
                return true;
            }
            if (next !== SP) {
                break;
            }
        }
    }
    if (current === SP) {
        var i = currentIndex;
        while (i > 0) {
            i--;
            var prev = classTypes[i];
            if (Array.isArray(a) ? a.indexOf(prev) !== -1 : a === prev) {
                var n = currentIndex;
                while (n <= classTypes.length) {
                    n++;
                    var next = classTypes[n];
                    if (next === b) {
                        return true;
                    }
                    if (next !== SP) {
                        break;
                    }
                }
            }
            if (prev !== SP) {
                break;
            }
        }
    }
    return false;
};
var previousNonSpaceClassType = function (currentIndex, classTypes) {
    var i = currentIndex;
    while (i >= 0) {
        var type = classTypes[i];
        if (type === SP) {
            i--;
        }
        else {
            return type;
        }
    }
    return 0;
};
var _lineBreakAtIndex = function (codePoints, classTypes, indicies, index, forbiddenBreaks) {
    if (indicies[index] === 0) {
        return exports.BREAK_NOT_ALLOWED;
    }
    var currentIndex = index - 1;
    if (Array.isArray(forbiddenBreaks) && forbiddenBreaks[currentIndex] === true) {
        return exports.BREAK_NOT_ALLOWED;
    }
    var beforeIndex = currentIndex - 1;
    var afterIndex = currentIndex + 1;
    var current = classTypes[currentIndex];
    // LB4 Always break after hard line breaks.
    // LB5 Treat CR followed by LF, as well as CR, LF, and NL as hard line breaks.
    var before = beforeIndex >= 0 ? classTypes[beforeIndex] : 0;
    var next = classTypes[afterIndex];
    if (current === CR && next === LF) {
        return exports.BREAK_NOT_ALLOWED;
    }
    if (HARD_LINE_BREAKS.indexOf(current) !== -1) {
        return exports.BREAK_MANDATORY;
    }
    // LB6 Do not break before hard line breaks.
    if (HARD_LINE_BREAKS.indexOf(next) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB7 Do not break before spaces or zero width space.
    if (SPACE.indexOf(next) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB8 Break before any character following a zero-width space, even if one or more spaces intervene.
    if (previousNonSpaceClassType(currentIndex, classTypes) === ZW) {
        return exports.BREAK_ALLOWED;
    }
    // LB8a Do not break after a zero width joiner.
    if (exports.UnicodeTrie.get(codePoints[currentIndex]) === ZWJ) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // zwj emojis
    if ((current === EB || current === EM) && exports.UnicodeTrie.get(codePoints[afterIndex]) === ZWJ) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB11 Do not break before or after Word joiner and related characters.
    if (current === WJ || next === WJ) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB12 Do not break after NBSP and related characters.
    if (current === GL) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB12a Do not break before NBSP and related characters, except after spaces and hyphens.
    if ([SP, BA, HY].indexOf(current) === -1 && next === GL) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB13 Do not break before ‘]’ or ‘!’ or ‘;’ or ‘/’, even after spaces.
    if ([CL, CP, EX, IS, SY].indexOf(next) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB14 Do not break after ‘[’, even after spaces.
    if (previousNonSpaceClassType(currentIndex, classTypes) === OP) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB15 Do not break within ‘”[’, even with intervening spaces.
    if (isAdjacentWithSpaceIgnored(QU, OP, currentIndex, classTypes)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB16 Do not break between closing punctuation and a nonstarter (lb=NS), even with intervening spaces.
    if (isAdjacentWithSpaceIgnored([CL, CP], NS, currentIndex, classTypes)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB17 Do not break within ‘——’, even with intervening spaces.
    if (isAdjacentWithSpaceIgnored(B2, B2, currentIndex, classTypes)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB18 Break after spaces.
    if (current === SP) {
        return exports.BREAK_ALLOWED;
    }
    // LB19 Do not break before or after quotation marks, such as ‘ ” ’.
    if (current === QU || next === QU) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB20 Break before and after unresolved CB.
    if (next === CB || current === CB) {
        return exports.BREAK_ALLOWED;
    }
    // LB21 Do not break before hyphen-minus, other hyphens, fixed-width spaces, small kana, and other non-starters, or after acute accents.
    if ([BA, HY, NS].indexOf(next) !== -1 || current === BB) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB21a Don't break after Hebrew + Hyphen.
    if (before === HL && HYPHEN.indexOf(current) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB21b Don’t break between Solidus and Hebrew letters.
    if (current === SY && next === HL) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB22 Do not break before ellipsis.
    if (next === IN) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB23 Do not break between digits and letters.
    if ((ALPHABETICS.indexOf(next) !== -1 && current === NU) || (ALPHABETICS.indexOf(current) !== -1 && next === NU)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB23a Do not break between numeric prefixes and ideographs, or between ideographs and numeric postfixes.
    if ((current === PR && [ID, EB, EM].indexOf(next) !== -1) ||
        ([ID, EB, EM].indexOf(current) !== -1 && next === PO)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB24 Do not break between numeric prefix/postfix and letters, or between letters and prefix/postfix.
    if ((ALPHABETICS.indexOf(current) !== -1 && PREFIX_POSTFIX.indexOf(next) !== -1) ||
        (PREFIX_POSTFIX.indexOf(current) !== -1 && ALPHABETICS.indexOf(next) !== -1)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB25 Do not break between the following pairs of classes relevant to numbers:
    if (
    // (PR | PO) × ( OP | HY )? NU
    ([PR, PO].indexOf(current) !== -1 &&
        (next === NU || ([OP, HY].indexOf(next) !== -1 && classTypes[afterIndex + 1] === NU))) ||
        // ( OP | HY ) × NU
        ([OP, HY].indexOf(current) !== -1 && next === NU) ||
        // NU ×	(NU | SY | IS)
        (current === NU && [NU, SY, IS].indexOf(next) !== -1)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // NU (NU | SY | IS)* × (NU | SY | IS | CL | CP)
    if ([NU, SY, IS, CL, CP].indexOf(next) !== -1) {
        var prevIndex = currentIndex;
        while (prevIndex >= 0) {
            var type = classTypes[prevIndex];
            if (type === NU) {
                return exports.BREAK_NOT_ALLOWED;
            }
            else if ([SY, IS].indexOf(type) !== -1) {
                prevIndex--;
            }
            else {
                break;
            }
        }
    }
    // NU (NU | SY | IS)* (CL | CP)? × (PO | PR))
    if ([PR, PO].indexOf(next) !== -1) {
        var prevIndex = [CL, CP].indexOf(current) !== -1 ? beforeIndex : currentIndex;
        while (prevIndex >= 0) {
            var type = classTypes[prevIndex];
            if (type === NU) {
                return exports.BREAK_NOT_ALLOWED;
            }
            else if ([SY, IS].indexOf(type) !== -1) {
                prevIndex--;
            }
            else {
                break;
            }
        }
    }
    // LB26 Do not break a Korean syllable.
    if ((JL === current && [JL, JV, H2, H3].indexOf(next) !== -1) ||
        ([JV, H2].indexOf(current) !== -1 && [JV, JT].indexOf(next) !== -1) ||
        ([JT, H3].indexOf(current) !== -1 && next === JT)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB27 Treat a Korean Syllable Block the same as ID.
    if ((KOREAN_SYLLABLE_BLOCK.indexOf(current) !== -1 && [IN, PO].indexOf(next) !== -1) ||
        (KOREAN_SYLLABLE_BLOCK.indexOf(next) !== -1 && current === PR)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB28 Do not break between alphabetics (“at”).
    if (ALPHABETICS.indexOf(current) !== -1 && ALPHABETICS.indexOf(next) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB29 Do not break between numeric punctuation and alphabetics (“e.g.”).
    if (current === IS && ALPHABETICS.indexOf(next) !== -1) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB30 Do not break between letters, numbers, or ordinary symbols and opening or closing parentheses.
    if ((ALPHABETICS.concat(NU).indexOf(current) !== -1 &&
        next === OP &&
        ea_OP.indexOf(codePoints[afterIndex]) === -1) ||
        (ALPHABETICS.concat(NU).indexOf(next) !== -1 && current === CP)) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB30a Break between two regional indicator symbols if and only if there are an even number of regional
    // indicators preceding the position of the break.
    if (current === RI && next === RI) {
        var i = indicies[currentIndex];
        var count = 1;
        while (i > 0) {
            i--;
            if (classTypes[i] === RI) {
                count++;
            }
            else {
                break;
            }
        }
        if (count % 2 !== 0) {
            return exports.BREAK_NOT_ALLOWED;
        }
    }
    // LB30b Do not break between an emoji base and an emoji modifier.
    if (current === EB && next === EM) {
        return exports.BREAK_NOT_ALLOWED;
    }
    return exports.BREAK_ALLOWED;
};
var lineBreakAtIndex = function (codePoints, index) {
    // LB2 Never break at the start of text.
    if (index === 0) {
        return exports.BREAK_NOT_ALLOWED;
    }
    // LB3 Always break at the end of text.
    if (index >= codePoints.length) {
        return exports.BREAK_MANDATORY;
    }
    var _a = exports.codePointsToCharacterClasses(codePoints), indices = _a[0], classTypes = _a[1];
    return _lineBreakAtIndex(codePoints, classTypes, indices, index);
};
exports.lineBreakAtIndex = lineBreakAtIndex;
var cssFormattedClasses = function (codePoints, options) {
    if (!options) {
        options = { lineBreak: 'normal', wordBreak: 'normal' };
    }
    var _a = exports.codePointsToCharacterClasses(codePoints, options.lineBreak), indicies = _a[0], classTypes = _a[1], isLetterNumber = _a[2];
    if (options.wordBreak === 'break-all' || options.wordBreak === 'break-word') {
        classTypes = classTypes.map(function (type) { return ([NU, AL, SA].indexOf(type) !== -1 ? ID : type); });
    }
    var forbiddenBreakpoints = options.wordBreak === 'keep-all'
        ? isLetterNumber.map(function (letterNumber, i) {
            return letterNumber && codePoints[i] >= 0x4e00 && codePoints[i] <= 0x9fff;
        })
        : undefined;
    return [indicies, classTypes, forbiddenBreakpoints];
};
var inlineBreakOpportunities = function (str, options) {
    var codePoints = Util_1.toCodePoints(str);
    var output = exports.BREAK_NOT_ALLOWED;
    var _a = cssFormattedClasses(codePoints, options), indicies = _a[0], classTypes = _a[1], forbiddenBreakpoints = _a[2];
    codePoints.forEach(function (codePoint, i) {
        output +=
            Util_1.fromCodePoint(codePoint) +
                (i >= codePoints.length - 1
                    ? exports.BREAK_MANDATORY
                    : _lineBreakAtIndex(codePoints, classTypes, indicies, i + 1, forbiddenBreakpoints));
    });
    return output;
};
exports.inlineBreakOpportunities = inlineBreakOpportunities;
var Break = /** @class */ (function () {
    function Break(codePoints, lineBreak, start, end) {
        this.codePoints = codePoints;
        this.required = lineBreak === exports.BREAK_MANDATORY;
        this.start = start;
        this.end = end;
    }
    Break.prototype.slice = function () {
        return Util_1.fromCodePoint.apply(void 0, this.codePoints.slice(this.start, this.end));
    };
    return Break;
}());
var LineBreaker = function (str, options) {
    var codePoints = Util_1.toCodePoints(str);
    var _a = cssFormattedClasses(codePoints, options), indicies = _a[0], classTypes = _a[1], forbiddenBreakpoints = _a[2];
    var length = codePoints.length;
    var lastEnd = 0;
    var nextIndex = 0;
    return {
        next: function () {
            if (nextIndex >= length) {
                return { done: true, value: null };
            }
            var lineBreak = exports.BREAK_NOT_ALLOWED;
            while (nextIndex < length &&
                (lineBreak = _lineBreakAtIndex(codePoints, classTypes, indicies, ++nextIndex, forbiddenBreakpoints)) ===
                    exports.BREAK_NOT_ALLOWED) { }
            if (lineBreak !== exports.BREAK_NOT_ALLOWED || nextIndex === length) {
                var value = new Break(codePoints, lineBreak, lastEnd, nextIndex);
                lastEnd = nextIndex;
                return { value: value, done: false };
            }
            return { done: true, value: null };
        },
    };
};
exports.LineBreaker = LineBreaker;
//# sourceMappingURL=LineBreak.js.map