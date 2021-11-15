"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.borderLeftStyle = exports.borderBottomStyle = exports.borderRightStyle = exports.borderTopStyle = exports.BORDER_STYLE = void 0;
var BORDER_STYLE;
(function (BORDER_STYLE) {
    BORDER_STYLE[BORDER_STYLE["NONE"] = 0] = "NONE";
    BORDER_STYLE[BORDER_STYLE["SOLID"] = 1] = "SOLID";
    BORDER_STYLE[BORDER_STYLE["DASHED"] = 2] = "DASHED";
    BORDER_STYLE[BORDER_STYLE["DOTTED"] = 3] = "DOTTED";
    BORDER_STYLE[BORDER_STYLE["DOUBLE"] = 4] = "DOUBLE";
})(BORDER_STYLE = exports.BORDER_STYLE || (exports.BORDER_STYLE = {}));
var borderStyleForSide = function (side) { return ({
    name: "border-" + side + "-style",
    initialValue: 'solid',
    prefix: false,
    type: 2 /* IDENT_VALUE */,
    parse: function (_context, style) {
        switch (style) {
            case 'none':
                return BORDER_STYLE.NONE;
            case 'dashed':
                return BORDER_STYLE.DASHED;
            case 'dotted':
                return BORDER_STYLE.DOTTED;
            case 'double':
                return BORDER_STYLE.DOUBLE;
        }
        return BORDER_STYLE.SOLID;
    }
}); };
exports.borderTopStyle = borderStyleForSide('top');
exports.borderRightStyle = borderStyleForSide('right');
exports.borderBottomStyle = borderStyleForSide('bottom');
exports.borderLeftStyle = borderStyleForSide('left');
//# sourceMappingURL=border-style.js.map