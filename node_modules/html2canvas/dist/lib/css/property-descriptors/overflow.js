"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.overflow = exports.OVERFLOW = void 0;
var parser_1 = require("../syntax/parser");
var OVERFLOW;
(function (OVERFLOW) {
    OVERFLOW[OVERFLOW["VISIBLE"] = 0] = "VISIBLE";
    OVERFLOW[OVERFLOW["HIDDEN"] = 1] = "HIDDEN";
    OVERFLOW[OVERFLOW["SCROLL"] = 2] = "SCROLL";
    OVERFLOW[OVERFLOW["CLIP"] = 3] = "CLIP";
    OVERFLOW[OVERFLOW["AUTO"] = 4] = "AUTO";
})(OVERFLOW = exports.OVERFLOW || (exports.OVERFLOW = {}));
exports.overflow = {
    name: 'overflow',
    initialValue: 'visible',
    prefix: false,
    type: 1 /* LIST */,
    parse: function (_context, tokens) {
        return tokens.filter(parser_1.isIdentToken).map(function (overflow) {
            switch (overflow.value) {
                case 'hidden':
                    return OVERFLOW.HIDDEN;
                case 'scroll':
                    return OVERFLOW.SCROLL;
                case 'clip':
                    return OVERFLOW.CLIP;
                case 'auto':
                    return OVERFLOW.AUTO;
                case 'visible':
                default:
                    return OVERFLOW.VISIBLE;
            }
        });
    }
};
//# sourceMappingURL=overflow.js.map